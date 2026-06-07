<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            {if isset($categories) && $categories|@count > 0}
                <div class="form-check">
                    {foreach from=$categories item=category}
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="categories[]" value="{$category.id}" id="category{$category.id}" form="articleForm" 
                                {if isset($article_categories) && in_array($category.id, $article_categories)}checked{/if}>
                            <label class="form-check-label" for="category{$category.id}">
                                {$category.name}
                            </label>
                        </div>
                    {/foreach}
                </div>
            {else}
                <p class="text-muted">No categories found.</p>
            {/if}
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </div>
        </div>
    </div>

    {* Modal for adding new category *}
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="addCategoryForm" autocomplete="off">
              <div class="mb-3">
                <label for="categoryName" class="form-label">Name</label>
                <input type="text" class="form-control" id="categoryName" name="name" maxlength="100" required>
              </div>
              <div class="mb-3">
                <label for="categorySlug" class="form-label">Slug</label>
                <input type="text" class="form-control" id="categorySlug" name="slug" maxlength="100" pattern="[a-z0-9-]+" required>
                <div class="form-text">Slug will be generated automatically but you can edit it.</div>
              </div>
              <div class="mb-3">
                <label for="categoryDescription" class="form-label">Description</label>
                <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
              </div>
              <div id="addCategoryError" class="alert alert-danger d-none"></div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveCategoryBtn" form="addCategoryForm">Save</button>
          </div>
        </div>
      </div>
    </div>
</div>
