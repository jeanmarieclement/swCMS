# Commenti separati per articoli e pagine (#40)

La migrazione `2026_09_06_000001_separate_comment_content_references.php` introduce
riferimenti distinti nella tabella `comments`:

| Colonna valorizzata | Significato |
| --- | --- |
| `post_id` | ID di un articolo in `posts` |
| `page_id` | ID di una pagina in `pages` |
| `legacy_post_id` | ID originale di un commento storico da verificare |

Esattamente una delle tre colonne deve essere valorizzata. Gli ID dei commenti,
il testo, lo stato di moderazione, gli autori e i collegamenti `parent_id` esistenti
sono conservati.

## Aggiornamento

1. Mettere il sito in manutenzione e fare un backup completo del database.
2. Installare il codice aggiornato ed eseguire, prima di riaprire il sito:

   ```sh
   php database/migrate.php up 2026_09_06_000001_separate_comment_content_references.php
   ```

3. Verificare l'esito della migrazione e la lista Commenti in amministrazione.

MySQL richiede i permessi `ALTER`, `INDEX`, `REFERENCES` e `TRIGGER`, oltre ai normali
permessi sui dati. La migrazione usa chiavi esterne con `ON DELETE CASCADE` per
entrambi i tipi di contenuto e indici `(post_id, status, parent_id)` e
`(page_id, status, parent_id)`. SQLite usa un `CHECK` per l'esclusività del
riferimento; MySQL usa due trigger `BEFORE INSERT/UPDATE`, poiché non consente un
`CHECK` sulle colonne coinvolte nelle azioni referenziali delle chiavi esterne
([documentazione MySQL](https://dev.mysql.com/doc/refman/8.0/en/create-table-check-constraints.html)).

SQLite ricostruisce la tabella in una transazione conservando colonne aggiuntive,
indici, trigger e vincoli preesistenti. Le connessioni SQLite devono abilitare
`PRAGMA foreign_keys = ON`, come già fa il core. Su MySQL il DDL non è
transazionale: non eseguire la migrazione mentre il sito riceve scritture.
Un'esecuzione interrotta può essere rilanciata per completare indici e trigger.

Il rollback automatico è intenzionalmente rifiutato: ricondurre i riferimenti a
un singolo `post_id` ripristinerebbe il difetto e potrebbe perdere commenti di
pagina. Per tornare al codice precedente ripristinare anche il backup precedente.

## Dati storici

La classificazione usa lo stato di `posts` e `pages` al momento della migrazione:

- ID presente solo in `posts`: mantenuto in `post_id`.
- ID presente solo in `pages`: spostato in `page_id`.
- ID presente in entrambe, oppure in nessuna: spostato in `legacy_post_id`;
  `post_id` e `page_id` diventano `NULL`.

Non si deduce il tipo dal testo, dal commento padre o dalla presenza del vecchio
vincolo verso `posts`. Un commento storico di pagina poteva essere stato salvato
proprio grazie alla coincidenza con l'ID di un articolo.

I commenti da verificare restano visibili in entrambe le viste amministrative,
con il loro ID originale. Non compaiono nel frontend, nelle risposte pubbliche o
nei conteggi per contenuto, anche se avevano stato `approved`. È possibile
segnalarli come spam o eliminarli, ma non approvarli o aggiungere risposte prima
di averne risolto l'associazione. Una successiva cancellazione di articolo o
pagina non li elimina né li riclassifica automaticamente.

Per elencarli:

```sql
SELECT id, legacy_post_id, parent_id, status, content
FROM comments
WHERE legacy_post_id IS NOT NULL
ORDER BY legacy_post_id, id;
```

La risoluzione richiede una verifica manuale contro il backup o la sorgente
WordPress. Dopo aver identificato il destinatario, aggiornare in un'unica
istruzione il riferimento corretto e azzerare `legacy_post_id`. Per esempio,
**solo dopo avere verificato che il commento 123 appartiene alla pagina 5**:

```sql
UPDATE comments
SET page_id = 5, post_id = NULL, legacy_post_id = NULL, status = 'pending'
WHERE id = 123 AND legacy_post_id = 5;
```

Risolvere esplicitamente ogni risposta nello stesso contenuto del padre;
se il collegamento storico è errato, impostare `parent_id = NULL`. La migrazione
non altera questi collegamenti. La vista gerarchica amministrativa mostra come
radici i commenti con padre assente dal filtro, incompatibile o ciclico, evitando
che scompaiano dalla moderazione; i riferimenti originali restano nel database.
La successiva approvazione segue il normale flusso amministrativo.

## API per plugin e importer

Usare `Comments::createComment()` con **solo** `post_id` oppure **solo** `page_id`.
`createReply()` verifica che un destinatario esplicito coincida con quello del
padre approvato; se il destinatario è omesso, lo eredita dal padre (flusso admin).
Un padre irrisolto è rifiutato. Anche `createComment()` valida `parent_id`.
Non usare `insert()`/SQL diretto per saltare la validazione delle gerarchie.

Le API `getApprovedForPost()`, `getApprovedHierarchicalForPost()` e
`countApprovedForPost()` continuano a riferirsi agli articoli. Le pagine usano
le corrispondenti API `*ForPage()`. Nei risultati amministrativi `post_title`
resta come alias compatibile del titolo, con `content_type` pari a `post`, `page`
o `unresolved`.

## Verifica

```sh
DB_DRIVER=sqlite php vendor/bin/phpunit --do-not-cache-result \
  tests/Integration/CommentContentIsolationTest.php
```

La matrice include SQLite e MySQL, con e senza i vincoli del vecchio schema.
Per MySQL impostare `COMMENTS_MYSQL_ADMIN_DSN`, `COMMENTS_MYSQL_USER` e
`COMMENTS_MYSQL_PASSWORD` su un server di test: ogni caso crea e rimuove un
database dal nome casuale `swcms_comments_test_*`. Senza queste variabili i casi
MySQL vengono saltati; CI e verifica release le forniscono esplicitamente.
