
	<div class="emergent">
		<div class="emergent_content">
		</div>
	</div>


	<div class="emergent_2">
		<div class="emergent_content_2">
		</div>
	</div>

	<div class="emergent_3">
		<div class="emergent_content_3">
		</div>
	</div>

{literal}
	<script type="text/javascript">
		function close_emergent(){
			$( ".emergent_content" ).html( '' );
			$( ".emergent" ).css( 'display', 'none' );
		}
		function close_emergent_2(){
			$( ".emergent_content_2" ).html( '' );
			$( ".emergent_2" ).css( 'display', 'none' );
		}
		function close_emergent_3(){
			$( ".emergent_content_3" ).html( '' );
			$( ".emergent_3" ).css( 'display', 'none' );
		}
	</script>

	<style type="text/css">
	.emergent,
	.emergent_2,
	.emergent_3{
		position: fixed;
		width: 100%;
		height: 100%;
		left : 0;
		top : 0;
		background-color: rgba( 0, 0, 0, .3);
		z-index:100;
		display: none;
	}
	.emergent_content,
	.emergent_content_2,
	.emergent_content_3{
		position: relative;
		top : 100px;
		width: 95%;
		left: 2.5%;
		background-color: white;
		box-shadow: 1px 1px 15px rgba( 0, 0, 0, .5 );
		padding: 10px;
		max-height: 80%;
		overflow-y : auto;
	}
	</style>
{/literal}