<?php
require_once __DIR__ . '/_admin_ui.php'; require_once __DIR__ . '/../../models/adminCategoryModel.php';
$categories=adminCategories();$editId=(int)($_GET['edit']??0);$edit=$editId?adminCategoryById($editId):null; adminPageStart('Categories','categories');
?>
<section class="page-head"><div><h1>Expense Categories</h1></div><button class="btn btn-gold" data-open-modal="categoryCreate">+ Add Category</button></section>
<section class="table-wrap"><table><thead><tr><th>ID</th><th>Category Name</th><th class="actions-col">Action</th></tr></thead><tbody>
<?php if(!$categories):?><tr><td colspan="3" class="empty">No categories yet.</td></tr><?php endif; ?>
<?php foreach($categories as $c):?><tr><td><?=(int)$c['category_id']?></td><td><?=ae($c['category_name'])?></td><td class="actions">
<a class="icon-link" href="categories.php?edit=<?=(int)$c['category_id']?>" title="Edit">&#9998;</a>
<form action="../../controllers/adminCategoryController.php" method="post" data-confirm="Delete this category?"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="category_id" value="<?=(int)$c['category_id']?>"><button class="icon-link danger" title="Delete">&#128465;</button></form>
</td></tr><?php endforeach;?>
</tbody></table></section>

<div class="modal-backdrop" id="categoryCreate" hidden><div class="form-modal"><button class="modal-x" data-close-modal>&times;</button><h2>Add category</h2><form action="../../controllers/adminCategoryController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="create">
<label><span>Category Name:</span><input name="category_name" required></label>
<div class="modal-actions"><button type="button" class="btn btn-gray" data-close-modal>Cancel</button><button class="btn btn-gold">Add</button></div>
</form></div></div>

<?php if($edit):?><div class="modal-backdrop show"><div class="form-modal"><a class="modal-x" href="categories.php">&times;</a><h2>Edit category</h2><form action="../../controllers/adminCategoryController.php" method="post"><input type="hidden" name="csrf_token" value="<?=ae(adminCsrfToken())?>"><input type="hidden" name="action" value="update"><input type="hidden" name="category_id" value="<?=(int)$edit['category_id']?>">
<label><span>Category Name:</span><input name="category_name" required value="<?=ae($edit['category_name'])?>"></label>
<div class="modal-actions"><a class="btn btn-gray" href="categories.php">Cancel</a><button class="btn btn-gold">Edit</button></div>
</form></div></div><?php endif;?>
<?php adminPageEnd(); ?>
