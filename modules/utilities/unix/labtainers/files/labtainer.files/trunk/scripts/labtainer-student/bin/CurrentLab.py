import json
import os
clab_file = '.tmp/current.json'
class CurrentLab():
    def __init__(self):
        self.data = {}
        self.load()
    def load(self):
        if os.path.isfile(clab_file):
            with open(clab_file) as fh: 
                try:
                    self.data = json.load(fh)
                except:
                    print('failed loading json %s' % clab_file)
                   
    def save(self):
        try:
            os.mkdir('./.tmp')
        except:
            pass
        with open(clab_file, 'w') as fh:
            json.dump(self.data, fh)
    def add(self, key, value):
        self.data[key] = value
    def get(self, key):
        if key in self.data:
            return self.data[key]
        else:
            return None
    def clear(self):
        self.data = {}
        self.save()
