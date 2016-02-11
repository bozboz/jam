<?php

$permissions->define([

	'view_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'create_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'delete_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',
	'edit_entity_type' => 'Bozboz\Permissions\Rules\ModelRule',

	'publish_entity' => 'Bozboz\Permissions\Rules\ModelRule',
	'unpublish_entity' => 'Bozboz\Permissions\Rules\ModelRule',

]);