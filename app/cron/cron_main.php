{
  "inbounds": [{
    "port": 2052,     
    "protocol": "vless",
    "settings": {
      "clients": [
        {
          "id": "32951367-2e23-4051-ba78-714573261b34",
          "alterId": 64"
        }
      ],
      "decryption": "none"
    },
    "streamSettings": {
      "network": "tcp", 
      "security": "none"
    }
  }],
  "outbounds": [{
    "protocol": "freedom",
    "settings": {}
  }]
}



{
  "inbounds": [{
    "port": 443,
    "protocol": "vmess",
    "settings": {
      "clients": [{
        "id": "UUID-شما-اینجا",
        "alterId": 64
      }]
    },
    "streamSettings": {
      "network": "tcp",
      "security": "tls",
      "tlsSettings": {
        "certificates": [{
          "certificateFile": "/etc/letsencrypt/live/yourdomain.com/fullchain.pem",
          "keyFile": "/etc/letsencrypt/live/yourdomain.com/privkey.pem"
        }]
      }
    }
  }],
  "outbounds": [{
    "protocol": "freedom",
    "settings": {}
  }]
}
