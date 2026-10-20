from http.server import BaseHTTPRequestHandler,HTTPServer
import json,uuid
from pathlib import Path
class Handler(BaseHTTPRequestHandler):
 def log_message(self,*args):pass
 def reply(self,data):
  body=json.dumps(data).encode();self.send_response(200);self.send_header('Content-Type','application/json');self.end_headers();self.wfile.write(body)
 def do_GET(self):
  if self.path.startswith('/api/sessions/'):self.reply({'status':'WORKING'})
  else:self.send_error(404)
 def do_POST(self):
  if self.path!='/api/sendText':self.send_error(404);return
  data=json.loads(self.rfile.read(int(self.headers.get('Content-Length',0))));assert data['session']=='ui-synthetic-session'
  ident='simulated-'+str(uuid.uuid4())
  with Path('/opt/infra-abrasil/backups/chat12/waha-simulated.jsonl').open('a') as log:log.write(json.dumps({'id':ident,'session':data['session'],'chatId':data['chatId'],'text':data['text']})+'\n')
  self.reply({'id':ident})
HTTPServer(('127.0.0.1',8015),Handler).serve_forever()
