// npm i amqplib
const amqp = require('amqplib');
(async () => {
  const conn = await amqp.connect('amqp://guest:guest@localhost:5672');
  const ch = await conn.createChannel();
  const q = 'graph-mail.outbound';
  await ch.assertQueue(q, {durable:true});
  const msg = {
    template_key: 'welcome.user',
    data: { name: 'Alice' },
    to: ['alice@example.com'],
    subject: 'Optional override'
  };
  ch.sendToQueue(q, Buffer.from(JSON.stringify(msg)), { contentType: 'application/json', persistent: true });
  console.log('Published');
  setTimeout(()=>{ ch.close(); conn.close(); }, 100);
})();
