/**
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-wc-orderbyvisits for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-orderbyvisits
 * @since 0.0.1
 */

jQuery( document ).ready( function( $ ) {
    $( "#odwpwcobv_generate_btn" ).prop( "disabled", false ).click( function() {
        $( this ).prop( "disabled", true );
        $( "#odwpwcobv_progress_img" ).fadeIn( "slow" );
        $( "#odwpwcobv_progress_msg" ).css( "visibility", "visible" );
        $.post(
            ajaxurl,
            { "action": "odwpwcobv_generate_random" },
            function( response ) {
                $( "#odwpwcobv_progress_img" ).fadeOut( "slow" );
                $( "#odwpwcobv_progress_msg" ).html( response === "OK" ? odwpwcobv.msg_ok : odwpwcobv.msg_err );
                $( "#odwpwcobv_generate_btn" ).prop( "disabled", false );
                setTimeout( function() { $( "#odwpwcobv_progress_msg" ).css( "visibility", "hidden" ); }, 3000 );
            }
        );
    } );
} );
