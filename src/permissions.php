<?php

$permissions->define([

	'view_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'create_entity_type' => 'Bozboz\Permissions\Rules\Rule',
	'delete_entity_type' => 'Bozboz\Permissions\Rules\Rule',
	'edit_entity_type' => 'Bozboz\Permissions\Rules\Rule',

	'publish_entity' => 'Bozboz\Permissions\Rules\Rule',
	'hide_entity' => 'Bozboz\Permissions\Rules\Rule',
	'schedule_entity' => 'Bozboz\Permissions\Rules\Rule',

]);