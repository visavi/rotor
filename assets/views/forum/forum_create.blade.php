@extends('layout')

@section('title', 'Форум - @parent')

@section('content')

	<h1>Создание новой темы</h1>
	<div class="form">
		<form action="/forum/create" method="post">
			<input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

			<div class="form-group{{ App::hasError('fid') }}">
				<label for="inputForum">Форум</label>
				<select class="form-control" id="inputForum" name="fid">

					<?php foreach ($forums[0] as $key => $data): ?>
					<?php $selected = ($fid == $data['forums_id']) ? ' selected="selected"' : ''; ?>
					<?php $disabled = ! empty($data['forums_closed']) ? ' disabled="disabled"' : ''; ?>
					<option value="<?=$data['forums_id']?>"<?=$selected?><?=$disabled?>><?=$data['forums_title']?></option>

					<?php if (isset($forums[$key])): ?>
					<?php foreach($forums[$key] as $datasub): ?>
					<?php $selected = $fid == $datasub['forums_id'] ? ' selected="selected"' : ''; ?>
					<?php $disabled = ! empty($datasub['forums_closed']) ? ' disabled="disabled"' : ''; ?>
					<option value="<?=$datasub['forums_id']?>"<?=$selected?><?=$disabled?>>– <?=$datasub['forums_title']?></option>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php endforeach; ?>

				</select>
				{!! App::textError('fid') !!}
			</div>

			<div class="form-group{{ App::hasError('title') }}">
				<label for="inputTitle">Название темы</label>
				<input name="title" type="text" class="form-control" id="inputTitle"  maxlength="50" placeholder="Название темы" value="{{ App::getInput('title') }}" required>
				{!! App::textError('title') !!}
			</div>

			<div class="form-group{{ App::hasError('msg') }}">
				<label for="markItUp">Сообщение:</label>
				<textarea class="form-control" id="markItUp" rows="5" name="msg" required>{{ App::getInput('msg') }}</textarea>
				{!! App::textError('msg') !!}
			</div>

			<button type="submit" class="btn btn-primary">Создать тему</button>
		</form>
	</div><br />

	Прежде чем создать новую тему необходимо ознакомиться с правилами<br />
	<a href="/rules">Правила сайта</a><br />
	Также убедись что такой темы нет, чтобы не создавать одинаковые, для этого введи ключевое слово в поиске<br />
	<a href="/forum/search">Поиск по форуму</a><br />
	И если после этого вы уверены, что ваша тема будет интересна другим пользователям, то можете ее создать<br /><br />

@stop
