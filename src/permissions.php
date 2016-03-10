<?php

$permissions->define([

	'publish_entity' => 'Bozboz\Permissions\Rules\Rule',
	'hide_entity' => 'Bozboz\Permissions\Rules\Rule',
	'schedule_entity' => 'Bozboz\Permissions\Rules\Rule',

]);

$entityTypes = Bozboz\Jam\Types\Type::whereVisible(true)->get();

foreach ($entityTypes as $type) {
	$permissions->define([
		'view_'.$type->alias => 'Bozboz\Permissions\Rules\ModelRule',
		'create_'.$type->alias => 'Bozboz\Permissions\Rules\ModelRule',
		'delete_'.$type->alias => 'Bozboz\Permissions\Rules\ModelRule',
		'edit_'.$type->alias => 'Bozboz\Permissions\Rules\ModelRule',
	]);
}