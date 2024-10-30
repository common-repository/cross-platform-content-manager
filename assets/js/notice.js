( function( wp ) {
  wp.data.subscribe( function() {
      var isSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
      var isAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
      var didPostSaveRequestSucceed = wp.data.select( 'core/editor' ).didPostSaveRequestSucceed();
      var didPostSaveRequestFail = wp.data.select( 'core/editor' ).didPostSaveRequestFail();

      if ( isSavingPost && !isAutosavingPost ) {
          if ( didPostSaveRequestSucceed ) {
              // Відображення успішного повідомлення
              wp.data.dispatch( 'core/notices' ).createSuccessNotice( 'Post updated and synchronized successfully.', { isDismissible: true } );
          } else if ( didPostSaveRequestFail ) {
              // Відображення повідомлення про помилку
              wp.data.dispatch( 'core/notices' ).createErrorNotice( 'Error occurred during synchronization.', { isDismissible: true } );
          }
      }
  } );
} )( window.wp );
