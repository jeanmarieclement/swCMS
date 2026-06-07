{* Admin User Profile Page *}
{extends file="admin/layout.tpl"}

{block name="title"}Your Profile{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$site_url}/admin">Dashboard</a></li>
        <li class="breadcrumb-item active">Profile</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Your Profile</h1>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Profile Information
                </h5>
            </div>
                <div class="card-body">
                    {* Flash Messages *}
                    {if isset($smarty.session.flash_message)}
                        <div class="alert alert-{if $smarty.session.flash_message.type == 'error'}danger{else}{$smarty.session.flash_message.type}{/if} alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {$smarty.session.flash_message.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    {/if}

                    {* Display errors *}
                    {if $error}
                        <div class="alert alert-danger" role="alert">
                            {$error|escape}
                        </div>
                    {/if}

                    <form method="post" action="{$site_url}/admin/profile">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        Username <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="{$user.username|escape}" required>
                                    <div class="form-text">This is used to login to your account</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="display_name" class="form-label">Display Name</label>
                                    <input type="text" class="form-control" id="display_name" name="display_name" 
                                           value="{$user.display_name|escape}" placeholder="{$user.username|escape}">
                                    <div class="form-text">This name will be shown publicly</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{$user.email|escape}" required>
                            <div class="form-text">Used for account recovery and notifications</div>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Change Password</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Leave blank to keep current password">
                                    <div class="form-text">Minimum 8 characters</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{$site_url}/admin" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>User ID:</strong></td>
                            <td>#{$user.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Role:</strong></td>
                            <td>
                                <span class="badge bg-{if $user.role == 'admin'}danger{elseif $user.role == 'editor'}warning{elseif $user.role == 'author'}info{else}secondary{/if}">
                                    {$user.role|capitalize}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Member Since:</strong></td>
                            <td>
                                {if $user.created_at|isset}
                                    {$user.created_at|date_format:"%d %B %Y"}
                                {else}
                                    ---
                                {/if}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Last Login:</strong></td>
                            <td>
                                {if $user.updated_at|isset}
                                    {$user.updated_at|date_format:"%d %B %Y at %H:%M"}
                                {else}
                                    ---
                                {/if}
                            </td>
                        </tr>

                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Security Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Use a strong, unique password</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Keep your email address up to date</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Log out from shared computers</small>
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <small>Review your account activity regularly</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
