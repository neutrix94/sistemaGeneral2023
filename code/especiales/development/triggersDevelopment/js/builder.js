
	function buildTriggers(){
		var resp = ``;
		for( trigger in current_triggers ){
			resp += makeTrigger( trigger );
			insert_trigger_count += ( current_triggers[trigger]['EVENT_MANIPULATION'] == 'INSERT' ? 1 : 0 );
			update_trigger_count += ( current_triggers[trigger]['EVENT_MANIPULATION'] == 'UPDATE' ? 1 : 0 );
			delete_trigger_count += ( current_triggers[trigger]['EVENT_MANIPULATION'] == 'DELETE' ? 1 : 0 );
		}
		if( insert_trigger_count == 0 ){
			current_triggers.push( new Array() );
			current_triggers[current_triggers.length - 1]['EVENT_MANIPULATION'] = "INSERT";
			current_triggers[current_triggers.length - 1]['ACTION_TIMING'] = "";
			current_triggers[current_triggers.length - 1]['TRIGGER_NAME'] = "";
			current_triggers[current_triggers.length - 1]['ACTION_STATEMENT'] = "ACTUALMENTE NO EXISTE";
			resp += makeTrigger( ( current_triggers.length - 1 ) );
		}
		if( update_trigger_count == 0 ){ 
			current_triggers.push( new Array() );
			current_triggers[current_triggers.length - 1]['EVENT_MANIPULATION'] = "UPDATE";
			current_triggers[current_triggers.length - 1]['ACTION_TIMING'] = "";
			current_triggers[current_triggers.length - 1]['TRIGGER_NAME'] = "";
			current_triggers[current_triggers.length - 1]['ACTION_STATEMENT'] = "ACTUALMENTE NO EXISTE";
			resp += makeTrigger( ( current_triggers.length - 1 ) );

		}
		if( delete_trigger_count == 0 ){
			current_triggers.push( new Array() );
			current_triggers[current_triggers.length - 1]['EVENT_MANIPULATION'] = "DELETE";
			current_triggers[current_triggers.length - 1]['ACTION_TIMING'] = "";
			current_triggers[current_triggers.length - 1]['TRIGGER_NAME'] = "";
			current_triggers[current_triggers.length - 1]['ACTION_STATEMENT'] = "ACTUALMENTE NO EXISTE";
			resp += makeTrigger( ( current_triggers.length - 1 ) );
		}
		return resp;
	}

	function makeTrigger( trigger ){
		var resp = `<div class="row text-center">
				<h4 class="text-center">${current_triggers[trigger]['EVENT_MANIPULATION']} ( ${current_triggers[trigger]['ACTION_TIMING']} )</h4>
				<div class="col-6 trigger_container">
					<div class="row">
						<div class="col-2">
							<b><i class=""></i>NOMBRE</b>
						</div>
						<div class="col-10">
							<input type="text" class="form-control" id="trigger_insert_name" value="${current_triggers[trigger]['TRIGGER_NAME']}" readonly>
						</div>
						<div class="col-2">
							<b><i class=""></i>TIEMPO</b>
						</div>
						<div class="col-10">
							<select class="form-control" id="trigger_insert_timing" readonly>
								<option value="0">--SELECCIONAR--</option>
								<option value="AFTER" ${current_triggers[trigger]['ACTION_TIMING'] == 'AFTER' ? ' selected' : ''}>DESPUES</option>
								<option value="BEFORE" ${current_triggers[trigger]['ACTION_TIMING'] == 'BEFORE' ? ' selected' : ''}>ANTES</option>
							</select>
						</div>
					</div>
					<textarea id="trigger_insert" class="form-control" style="height:auto;" readonly>${current_triggers[trigger]['ACTION_STATEMENT']}</textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
				<div class="col-6 trigger_container">
					<div class="row">
						<div class="col-2">
							<b><i class=""></i>NOMBRE</b>
						</div>
						<div class="col-10">
							<input type="text" class="form-control" value="${current_triggers[trigger]['TRIGGER_NAME']}">
						</div>
						<div class="col-2">
							<b><i class=""></i>TIEMPO</b>
						</div>
						<div class="col-10">
							<select class="form-control">
								<option value="0">--SELECCIONAR--</option>
								<option value="AFTER" ${current_triggers[trigger]['ACTION_TIMING'] == 'AFTER' ? ' selected' : ''}>DESPUES</option>
								<option value="BEFORE" ${current_triggers[trigger]['ACTION_TIMING'] == 'BEFORE' ? ' selected' : ''}>ANTES</option>
							</select>
						</div>
					</div>
					<textarea id="trigger_insert_new" class="form-control" style="height:auto;"></textarea>
					<button
						type="button"
						class="btn btn-success"
					>
						<i>Descargar</i>
					</button>
				</div>
			</div>`;
		return resp;
	}