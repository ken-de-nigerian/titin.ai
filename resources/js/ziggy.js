const Ziggy = {"url":"https:\/\/titin.ai.test","port":null,"defaults":{},"routes":{"boost.browser-logs":{"uri":"_boost\/browser-logs","methods":["POST"]},"home":{"uri":"\/","methods":["GET","HEAD"]},"dashboard":{"uri":"dashboard","methods":["GET","HEAD"]},"interview":{"uri":"interview","methods":["GET","HEAD"]},"feedback":{"uri":"feedback","methods":["GET","HEAD"]},"storage.local":{"uri":"storage\/{path}","methods":["GET","HEAD"],"wheres":{"path":".*"},"parameters":["path"]},"storage.local.upload":{"uri":"storage\/{path}","methods":["PUT"],"wheres":{"path":".*"},"parameters":["path"]}}};

if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
  Object.assign(Ziggy.routes, window.Ziggy.routes);
}

export { Ziggy };
