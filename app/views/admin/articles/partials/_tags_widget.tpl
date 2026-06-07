<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tags</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <!-- Multi-select for tags -->
            <select id="tags" name="tags[]" class="select2" multiple="multiple" form="articleForm" style="width:100%">
            
                {if isset($tags) && $tags}
                    {foreach from=$tags item=tag}
                        <option value="{$tag.name}" selected>{$tag.name}</option>
                    {/foreach}
                {/if}
            </select>
            <div class="form-text">Start typing to search or add tags</div>
            <!-- END Select2 tag widget -->
        </div>
    </div>
</div>
