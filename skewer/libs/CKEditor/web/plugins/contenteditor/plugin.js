/**
 * Copyright (c) 2014-2016, CKSource - Frederico Knabben. All rights reserved.
 * Licensed under the terms of the MIT License (see LICENSE.md).
 *
 * Basic sample plugin inserting abbreviation elements into the CKEditor editing area.
 *
 * Created out of the CKEditor Plugin SDK:
 * http://docs.ckeditor.com/#!/guide/plugin_sdk_sample_1
 */

// Register the plugin within the editor.
CKEDITOR.plugins.add( 'contenteditor', {

	lang: ['en','ru','de'],
	// Register the icons.
	icons: 'contenteditor',

	// The plugin initialization logic goes inside this method.
	init: function( editor ) {

		// Define an editor command that opens our dialog window.
		editor.addCommand( 'contenteditor', new CKEDITOR.dialogCommand( 'contenteditorDialog' ) );

		// Create a toolbar button that executes the above command.
		editor.ui.addButton( 'Contenteditor', {

			// The text part of the button (if available) and the tooltip.
			label: editor.lang.contenteditor.insert_block,

			// The command to execute on click.
			command: 'contenteditor',

			// The button placement in the toolbar (toolbar group name).
			toolbar: 'insert'
		});

		if ( editor.contextMenu ) {
			
			// Add a context menu group with the Edit Abbreviation item.
			editor.addMenuGroup( 'contenteditorGroup' );
			editor.addMenuItem( 'contenteditorItem', {
				label: 'Edit Abbreviation',
				icon: this.path + 'icons/abbr.png',
				command: 'contenteditor',
				group: 'contenteditorGroup'
			});

			editor.contextMenu.addListener( function( element ) {
				if ( element.getAscendant( 'contenteditor', true ) ) {
					return { contenteditorItem: CKEDITOR.TRISTATE_OFF };
				}
			});
		}

		// Register our dialog file -- this.path is the plugin folder path.
		CKEDITOR.dialog.add( 'contenteditorDialog', this.path + 'dialogs/contenteditor.js' );
	}
});
