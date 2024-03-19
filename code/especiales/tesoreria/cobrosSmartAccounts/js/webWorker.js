// miWorker.js

self.addEventListener('message', function (e) {
    const data = e.data;

    // Simula la lógica del Web Worker
    const result = performWebWorkerTask(data);

    // Envía el resultado de vuelta al hilo principal
    self.postMessage(result);
});

function performWebWorkerTask(data) {
    // Realiza las tareas intensivas del Web Worker aquí
    // Puedes realizar operaciones costosas sin bloquear el hilo principal
    return 'Tarea del Web Worker completada con: ' + data;
}