<?php
	
	class FrontEnd
	{
		
		function __construct(  )
		{

		}

		public function buildTablesCatalogue( $stm ){
			$counter = 1;
			echo "<div class=\"col-3 tables_list\">";
				echo "<div class=\"title_sticky\">TABLAS</div>";//tables_catalogue
			while ( $row = $stm->fetch_assoc() ) {
				echo "<div class=\"group_card\" onclick=\"getTableStructure( '{$row['TABLE_NAME']}' );\">{$counter} - {$row['TABLE_NAME']} - T : {$row['TRIGGERS_COUNTER']} </div>";
				$counter ++;
			}
			echo "</div>";
		}

		public function buildTableStructure( $table, $stm ){
			$counter = 0;
			//echo "<div class=\"col-3\" style=\"border : 1px solid;\">";//tables_catalogue
			echo "<div class=\"row\">
				<div class=\"col-1 text-center\">#</div>
				<div class=\"col-7 text-center\">Campo</div>
				<div class=\"col-1 text-center icon-key-4\"></div>
			</div>";
			echo "<div id=\"fields_container\">";
			while ( $row = $stm->fetch_assoc() ) {
				echo "<div class=\"row group_card\" style=\"width : 100%;\">
						<div class=\"col-1\">{$row['ORDINAL_POSITION']}</div>
						<div class=\"col-7\">
							<label for=\"valid_row_{$counter}\">
								{$row['COLUMN_NAME']} <b class=\"row_type\">{$row['COLUMN_TYPE']}</b>
							</label>

						</div>
						<div class=\"col-1\">
							<input  type=\"checkbox\" id=\"valid_row_{$counter}\" value=\"{$row['COLUMN_NAME']}\" checked>
						</div>
						<div class=\"col-1\">
							<input type=\"radio\" id=\"key_row_{$counter}\" value=\"{$row['COLUMN_NAME']}\" name=\"pk\">
						</div>
					</div>";
				$counter ++;
			}
			echo "</div>";
			echo "<br>
				<div class=\"text-center\">
					<button 
						type=\"button\" 
						class=\"btn btn-success\" 
						onclick=\"makeTriggers( '{$table}' )\"
					>
						<i class=\"icon-ok-circle\">Generar Triggers</i>
					</button>
				</div>";
		}

	}

?>