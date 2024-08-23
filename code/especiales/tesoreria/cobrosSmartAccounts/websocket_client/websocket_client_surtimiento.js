
import events from './utils/constants_surtimiento.js';
import Queue from './utils/queue.js';

const connectWebSocket = (ws_ref) => {
  const PING_TIMEOUT = 1000 * 5 + 1000 * 1;
  const QUEUE_INTERVAL = 1000 * 30 + 1000 * 1;
  const RECONNECT_INTERVAL = 1000 * 10 + 1000 * 1;
  const PING_VALUE = 1;

  const ACKNOWLEDGEMENT_EVENTS_CLIENTS = [
    events.INFORM_VIEWED_TRANSACTION,
    events.INFORM_FOLIO,
  ];

  /*var ws = new WebSocket('ws://localhost:3000', [
    '7dff3c34-faee-11ea-a7be-3d014d7f956c',
    'true',
  ]);*/
  //ws = new WebSocket( $url_websocket + `${$token_websocket}`);
  //$urlWS = 
  
  ws = new WebSocket( `wss://30rwczw5-3001.usw3.devtunnels.ms?user=${$user_id}&type=${$perfil_usuario}`);
  /*
    , [
    $token_websocket,
    $usuario_websocket,
    $sucursal_websocket
  ]
  */
  ws.isProcessing = false;
  ws.eventQueue = ws_ref ? ws_ref.eventQueue : new Queue();
  ws.actualNotification = ws_ref ? ws_ref.actualNotification : null;
  ws.viewedNotification = ws_ref ? ws_ref.viewedNotification : [];

  const isBinary = (obj) => {
    return (
      typeof obj === 'object' &&
      Object.prototype.toString.call(obj) === '[object Blob]'
    );
  };

  const ping = () => {
    if (!ws) {
      return;
    } else if (!!ws.pingTimeout) {
      clearTimeout(ws.pingTimeout);
    }

    ws.pingTimeout = setTimeout(() => {
      ws.close();
      window.reconnectInterval = setInterval(
        reconnectInterval,
        RECONNECT_INTERVAL,
      );
    }, PING_TIMEOUT);

    const data = new Uint8Array(1);
    data[0] = PING_VALUE;
    ws.send(data);
  };

  const reconnectInterval = () => {
    if (ws.readyState === ws.CLOSED) {
      connectWebSocket(ws);
    }
  };

  ws.queueInterval = setInterval(() => {
    console.log(
      'Ejecutando queue con processing: ',
      ws.isProcessing,
      ' queue: ',
      ws.eventQueue,
    );
    ws.eventQueue.get().forEach((event) => {
      if (!ws.isProcessing && ws.queueFunction) {
        ws.queueFunction(event);
      }
    });
  }, QUEUE_INTERVAL);

  ws.sendAcknowledgment = (eventType) => {
    ws.send(
      JSON.stringify({
        type: eventType,
      }),
    );
  };

  ws.addEventListener('open', (event) => {
    clearInterval(window.reconnectInterval);
    window.reconnectInterval = null;
    if (ws.onConnection) {
      ws.onConnection();
    }
    console.log(`Connection started`);
  });

  ws.addEventListener('error', (event) => {
    console.log('Error on WS: ', event);
  });

  ws.addEventListener('message', (event) => {
    if (isBinary(event.data)) {
      ping();
    } else {
      let jsonMsg = JSON.parse(event.data);
      if (jsonMsg) {
        console.log(`Received msg: `, jsonMsg);
        if (ws.msgFunction) {
          ws.msgFunction(jsonMsg);
        }
      }
    }
  });

  ws.addEventListener('close', () => {
    console.log(`Connection closed`);

    if (!!ws.pingTimeout) {
      clearTimeout(ws.pingTimeout);
    }

    clearInterval(ws.queueInterval);
    if (!window.reconnectInterval) {
      reconnectInterval();
      window.reconnectInterval = setInterval(
        reconnectInterval,
        RECONNECT_INTERVAL,
      );
    }
  });

  ws.queueFunction = (event) => {
    if (event === events.UPDATE_VIEWED_NOTIFICATION) {
      ws.sendViewedNotifications();
    }
  };

  ws.sendViewedNotifications = () => {
    ws.isProcessing = true;
    ws.send(
      JSON.stringify({
        type: events.UPDATE_VIEWED_NOTIFICATION,
        notification: ws.viewedNotification,
      }),
    );
    ws.isProcessing = false;
    ws.eventQueue.add(events.UPDATE_VIEWED_NOTIFICATION);
  };

  ws.informNotification = () => {
    ws.isProcessing = true;
    console.log(ws.actualNotification);
    ws.send(
      JSON.stringify({
        type: events.ACTUAL_NOTIFICATION,
        notification: ws.actualNotification,
      }),
    );
    ws.isProcessing = false;
    ws.eventQueue.add(events.ACTUAL_NOTIFICATION);
  };
//aqui llega respuesta
  ws.msgFunction = (jsonMsg) => {
    
    //ACKNOWLEDGEMENT_EVENTS_CLIENTSif (jsonMsg.type == events.INFORM_VIEWED_TRANSACTION) {
    if (ACKNOWLEDGEMENT_EVENTS_CLIENTS.includes(jsonMsg.type)) {
      ws.eventQueue.remove(jsonMsg.type);
    } else if ( jsonMsg.type == events.INFORM_NOTIFICATIONS ) {//aqui llegan las transacciones
      console.log(jsonMsg.notifications);
      let folios = [];

//       jsonMsg.transactions.forEach((transaction) => {//aqui esta la respuesta de transacciones
// /*habilitado por oscar 2024-07-01 para no ver en la vista las transacciones pendientes*/
//       $( '#stop' ).click();
//       //aqui brinca la emergente
//       //aqui brinca la emergente
//         $( ".emergent_content" ).html( `<div class="text-center bg-danger">
//           <br>
//           <br>
//           <h2 class="text-light text-center">${transaction.message} ( ${transaction.traceability ? transaction.traceability.folio_venta : transaction.folio_venta } ) </h2>
//           <h2 class="text-light text-center">Recargar la pagina y volver a escanear el ticket</h2>
//           <br>
//           <br>
//           <div class="row text-center">
//             <div class="col-3"></div>
//             <div class="col-6">
//               <button
//                 type="button"
//                 class="btn btn-warning form-control"
//                 style="font-size:200%;"
//                 onclick="marcar_notificacion_vista( '${transaction.traceability ? transaction.traceability.folio_unico_transaccion : transaction.folio_unico }', ${transaction.traceability ? true : false } );"
//               ><i class="icon-spin3">OK</i>
//               </button>
//               <br>
//               <br>
//               </div>
//             </div>
//           </div>` );
//         $( ".emergent" ).css( "display", "block" );//deshabilitado por Oscar marcar_notificacion_vista( '${transaction.traceability ? transaction.traceability.folio_unico_transaccion : transaction.folio_unico }' );
// //desarrollar boton para indicador de visto
//         folios.push(transaction.folio_unico);
//       });

      ws.sendAcknowledgment(jsonMsg.type);
      //ws.viewedFolios = [...folios, ...ws.viewedFolios];
      //ws.sendViewedTransactions();
    } else if (jsonMsg.type == events.ACTUAL_TRANSACTION) {
      console.log(jsonMsg.notification);
      ws.sendAcknowledgment(jsonMsg.type);
      // ws.actualTransaction = jsonMsg.transaction;//aqui esta la respuesta de transacciones
      // //aqui brinca la emergente
      //   $( '#stop' ).click();
      //   $( ".emergent_content" ).html( `<h2 class="text-success text-center">${ws.actualTransaction.message}</h2>
			// 	<div class="text-center">
			// 		<button
			// 			type="button"
			// 			class="btn btn-success"
			// 			onclick="marcar_notificacion_vista( '${ws.actualTransaction.traceability.folio_unico_transaccion}', ${ws.actualTransaction.message.trim() == 'Transacción exitosa' || jsonMsg.transaction.message.trim() == 'Transaccion exitosa' ? true : false } );"
			// 		><i class="icon-ok=circle">Aceptar y marcar notificación como vista</i>
			// 		</button>
      //     </div>` );
      //   $( ".emergent" ).css( "display", "block" );
    }
  };
};

connectWebSocket();