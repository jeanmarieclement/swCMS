{* Page Attributes Partial *}
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Page Attributes</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="parent_id" class="form-label">Parent Page</label>
            <select class="form-select" id="parent_id" name="parent_id">
                <option value="0">None</option>
                {if isset($pages)}
                    {foreach from=$pages item=p}
                        {if !isset($page.id) || $p.id != $page.id}
                            <option value="{$p.id}" {if isset($page.parent_id) && $page.parent_id == $p.id}selected{/if}>{$p.title}</option>
                        {/if}
                    {/foreach}
                {/if}
            </select>
        </div>
        
        <div class="mb-3">
            <label for="template" class="form-label">Template</label>
            <select class="form-select" id="template" name="template">
                <option value="default" {if isset($page.template) && $page.template == 'default'}selected{/if}>Default Template</option>
                <option value="full-width" {if isset($page.template) && $page.template == 'full-width'}selected{/if}>Full Width</option>
                <option value="sidebar" {if isset($page.template) && $page.template == 'sidebar'}selected{/if}>With Sidebar</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="order" class="form-label">Order</label>
            <input type="number" class="form-control" id="order" name="order" value="{if isset($page.order)}{$page.order}{else}0{/if}">
        </div>
    </div>
</div>
