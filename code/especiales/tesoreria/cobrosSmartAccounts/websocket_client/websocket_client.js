
import events from './utils/constants.js';
import Queue from './utils/queue.js';

const connectWebSocket = (ws_ref) => {
  const PING_TIMEOUT = 1000 * 5 + 1000 * 1;
  const QUEUE_INTERVAL = 1000 * 30 + 1000 * 1;
  const RECONNECT_INTERVAL = 1000 * 10 + 1000 * 1;
  const PING_VALUE = 1;
  const MAX_TRIES = 3;
  var reconnectTries = 0;

  /*var ws = new WebSocket('ws://localhost:3000', [
    '7dff3c34-faee-11ea-a7be-3d014d7f956c',
    'true',
  ]);*/
  ws = new WebSocket( $url_websocket + `${$token_websocket}`);
  /*
    , [
    $token_websocket,
    $usuario_websocket,
    $sucursal_websocket
  ]
  */
  ws.isProcessing = false;
  ws.eventQueue = ws_ref ? ws_ref.eventQueue : new Queue();
  ws.actualTransaction = ws_ref ? ws_ref.actualTransaction : null;
  ws.currentTransaction = ws_ref ? ws_ref.currentTransaction : null;
  ws.viewedFolios = ws_ref ? ws_ref.actualTransaction : [];

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
    if (ws.readyState === ws.CLOSED && reconnectTries < MAX_TRIES) {
      reconnectTries++;
      connectWebSocket(ws);
    } else if (reconnectTries >= MAX_TRIES) {
      clearInterval(window.reconnectInterval);
      window.reconnectInterval = null;
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
    reconnectTries = 0;
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
      window.reconnectInterval = setInterval(
        reconnectInterval,
        RECONNECT_INTERVAL,
      );
    }
  });

  ws.queueFunction = (event) => {
    if (event === event.INFORM_VIEWED_TRANSACTION) {
      ws.sendViewedTransactions();
    } else if (event === event.GET_TRANSACTION_STATUS) {
      ws.refreshTransaction();
    }
  };

  ws.sendViewedTransactions = () => {
    ws.isProcessing = true;
    ws.send(
      JSON.stringify({
        type: events.INFORM_VIEWED_TRANSACTION,
        folios: ws.viewedFolios,
      }),
    );
    ws.isProcessing = false;
    ws.eventQueue.add(events.INFORM_VIEWED_TRANSACTION);
  };

  ws.refreshTransaction = () => {
    ws.isProcessing = true;
    ws.send(
      JSON.stringify({
        type: events.GET_TRANSACTION_STATUS,
        payload: ws.currentTransaction,
      }),
    );
    ws.isProcessing = false;
    ws.eventQueue.add(events.GET_TRANSACTION_STATUS);
  };
//aqui llega respuesta
  ws.msgFunction = (jsonMsg) => {
    if (jsonMsg.type == events.INFORM_VIEWED_TRANSACTION) {
      ws.eventQueue.remove(jsonMsg.type);
    } else if (jsonMsg.type == events.INFORM_TRANSACTIONS) {//aqui llegan las transacciones
      let folios = [];
      jsonMsg.transactions.forEach((transaction) => {//aqui esta la respuesta de transacciones
      //aqui brinca la emergente
        $( ".emergent_content" ).html( `<h2 class="text-success text-center">${transaction.message}</h2>
				<div class="text-center">
					<button
						type="button"
						class="btn btn-success"
						onclick="marcar_notificacion_vista( '${transaction.traceability.folio_unico_transaccion}' );"
					><i class="icon-ok=circle">Aceptar y marcar notificacion como vista</i>
					</button>
          </div>` );
        $( ".emergent" ).css( "display", "block" );
//desarrollar boton para indicador de visto
        folios.push(transaction.folio_unico);
      });

      ws.sendAcknowledgment(jsonMsg.type);
      //ws.viewedFolios = [...folios, ...ws.viewedFolios];
      //ws.sendViewedTransactions();
    } else if (jsonMsg.type == events.ACTUAL_TRANSACTION) {
      ws.sendAcknowledgment(jsonMsg.type);
      ws.actualTransaction = jsonMsg.transaction;//aqui esta la respuesta de transacciones
      //aqui brinca la emergente
        $( ".emergent_content" ).html( `<h2 class="text-success text-center">${ws.actualTransaction.message}</h2>
				<div class="text-center">
					<button
						type="button"
						class="btn btn-success"
						onclick="marcar_notificacion_vista( '${ws.actualTransaction.traceability.folio_unico_transaccion}' );"
					><i class="icon-ok=circle">Aceptar y marcar notificacion como vista</i>
					</button>
          </div>` );
        $( ".emergent" ).css( "display", "block" );
    } else if (jsonMsg.type == events.SEND_TRANSACTION_STATUS) {
      ws.sendAcknowledgment(jsonMsg.type);
      console.log( jsonMsg.transaction );
     //aqui brinca la emergente
      $( ".emergent_content" ).html( `<h2 class="text-success text-center">${jsonMsg.transaction.message}</h2>
      <div class="text-center">
        <button
          type="button"
          class="btn btn-success"
          onclick="marcar_notificacion_vista( '${jsonMsg.transaction.folio_unico}' );"
        ><i class="icon-ok=circle">Aceptar y marcar notificacion como vista</i>
        </button>
        </div>` );
      $( ".emergent" ).css( "display", "block" );
      //console.log(jsonMsg);
    }
  };
};

connectWebSocket();
