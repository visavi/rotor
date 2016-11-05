<h3>Поиск запроса &quot;<?=$find?>&quot; в тексте</h3>
Найдено совпадений: <b><?=$total?></b><br /><br />

<?php foreach ($blogs as $data): ?>

	<div class="b">
		<i class="fa fa-pencil"></i>
		<b><a href="/blog/blog?act=view&amp;id=<?=$data['id']?>"><?=$data['title']?></a></b> (<?=format_num($data['rating'])?>)
	</div>

	<?php if (utf_strlen($data['text']) > 200) {
		$data['text'] = strip_tags(bb_code($data['text']), '<br>');
		$data['text'] = utf_substr($data['text'], 0, 200).'...';
	} ?>

	<div>
		<?=$data['text']?><br />

		Категория: <a href="/blog/blog?cid=<?=$data['id']?>"><?=$data['name']?></a><br />
		Автор: <?=profile($data['user'])?> (<?=date_fixed($data['time'])?>)
	</div>
<?php endforeach; ?>
