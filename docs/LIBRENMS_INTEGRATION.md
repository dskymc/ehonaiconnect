# Integrasi LibreNMS — e-Honai Connect

## Variabel `.env`

```ini
LIBRENMS_URL = https://librenms.example.go.id
LIBRENMS_API_TOKEN = your_read_only_api_token
LIBRENMS_WEBHOOK_SECRET = random_secret_string_here
librenms.enabled = true
librenms.cacheTtl = 120
librenms.webhookAllowIps = 10.0.0.5
librenms.httpTimeout = 15
```

Token API dibuat di LibreNMS: **Settings → API Settings → Create API access token** (user dengan hak baca device).

## Webhook Alert Transport (LibreNMS)

1. **Alerts → Alert Transports → Add Transport → API**
2. **API Method:** `POST`
3. **API URL:** `https://your-ehona-domain/webhook/librenms`
4. **API Headers:**
   ```
   X-Webhook-Token: {LIBRENMS_WEBHOOK_SECRET}
   Content-Type: application/json
   ```
5. **API Body (JSON):**
   ```json
   {
     "hostname": "{{ $hostname }}",
     "device_id": "{{ $device_id }}",
     "title": "{{ $title }}",
     "msg": "{{ $msg }}",
     "severity": "{{ $severity }}",
     "state": "{{ $state }}",
     "alert_id": "{{ $id }}"
   }
   ```

6. Hubungkan transport ke **Alert Rules** (device down, port down, dll.).

## Sinkron perangkat (opsional)

```bash
php spark monitoring:sync
```

Jadwalkan via Task Scheduler (Windows) atau cron setiap 15 menit.
