<?php

$permissions->define([

	'publish_entity' => 'Bozboz\Permissions\Rules\Rule',
	'hide_entity' => 'Bozboz\Permissions\Rules\Rule',
	'schedule_entity' => 'Bozboz\Permissions\Rules\Rule',

	'manage_entities' => 'Bozboz\Permissions\Rules\Rule',

	'view_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'create_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'delete_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'edit_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',

]);
