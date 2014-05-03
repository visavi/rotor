<h3>Поиск запроса &quot;<?=$find?>&quot; в тексте</h3>
Найдено совпадений: <b><?=$total?></b><br /><br />

<?php foreach ($blogs as $data): ?>

	<div class="b">
		<img src="/images/img/edit.gif" alt="image" />
		<b><a href="blog.php?act=view&amp;id=<?=$data['blogs_id']?>"><?=$data['blogs_title']?></a></b> (<?=format_num($data['blogs_rating'])?>)
	</div>

	<?php if (utf_strlen($data['blogs_text']) > 200) {
		$data['blogs_text'] = strip_tags(bb_code($data['blogs_text']), '<br>');
		$data['blogs_text'] = utf_substr($data['blogs_text'], 0, 200).'...';
	} ?>

	<div>
		<?=$data['blogs_text']?><br />

		Категория: <a href="blog.php?cid=<?=$data['cats_id']?>"><?=$data['cats_name']?></a><br />
		Автор: <?=profile($data['blogs_user'])?> (<?=date_fixed($data['blogs_time'])?>)
	</div>
<?php endforeach; ?>
