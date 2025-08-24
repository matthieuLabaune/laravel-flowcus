# 📘 Timeline MVP - Specs & TODO

## 1. Spécifications (Specs)

### A. Objectif
Créer un MVP web de l’application **Timeline**, permettant de gérer des souvenirs variés (photos, notes, liens, etc.), organisés automatiquement dans un calendrier et regroupables en événements.

### B. Technologies
- **Backend** : Laravel 12 + Breeze (Inertia + Vue 3 + TS) + TailwindCSS
- **Base de données** : MySQL/MariaDB
- **Gestion fichiers** : Storage Laravel (disk public → S3 plus tard)
- **Médias** : Intervention/Image pour images, EXIF pour métadonnées

### C. Modèle de données
- **entries** : table principale, un souvenir (photo, note, lien, audio, etc.)
  - `id`, `user_id`, `type`, `occurred_at`, `title`, `note`, `payload (JSON)`, `lat/lng`, `place_name`
- **attachments** : fichiers associés (images, vidéos, audios, docs)
  - `id`, `entry_id`, `kind`, `disk`, `path`, `preview_path`, `mime`, `width/height/duration`, `meta (JSON)`
- **events** : regroupements de plusieurs entries
  - `id`, `user_id`, `title`, `description`, `start_date`, `end_date`
- **event_entry (pivot)** : table de relation entries ↔ events

### D. Types d’entries
- `photo` : image + exif
- `note` : texte libre + mood/tags
- `link` : URL + metadata
- `audio` : fichier audio + durée
- `video` : fichier vidéo + dimensions/durée
- `checkin` : localisation seule
- `doc` : document attaché

### E. Fonctionnalités MVP
1. **Authentification** (Breeze Inertia)
2. **Calendrier** mensuel → affichage des jours contenant des entries
3. **Jour** → liste des entries du jour (cards dynamiques par type)
4. **Entry**
   - Création : photo (upload), note (texte), lien (URL)
   - Affichage : rendu adapté selon type
   - Edition : titre, note, tags
5. **Événement**
   - Création manuelle (sélection d’entries existants)
   - Page événement : titre, description, période, liste entries, carte
6. **Import**
   - Système générique d’import (Job + Service)
   - Premier importer : CSV simple (date, type, title, note, url, lat, lng)

### F. Règles & contraintes
- Toutes les données sont **privées par défaut** (pas de partage public dans MVP).
- Dates → `occurred_at` obligatoire (EXIF ou fallback `now()`).
- Suppression → cascade (entries → attachments).
- Indexes pour perf :
  - `(user_id, occurred_at)`
  - `(user_id, type, occurred_at)`

---

## 2. Checklist TODO

### Setup
- [ ] Installer Laravel 12 + Breeze (Inertia Vue 3 + TS + Tailwind)
- [ ] Configurer stockage local (public) + Intervention/Image
- [ ] Créer migrations : `entries`, `attachments`, `events`, `event_entry`

### Backend
- [ ] Modèles : Entry, Attachment, Event
- [ ] Enum `EntryType` (photo, note, link, …)
- [ ] Relations : Entry → Attachments, Event → Entries
- [ ] Service `EntryService` (création selon type)
- [ ] Extracteur EXIF (helper `extractGps()`)
- [ ] Endpoint API JSON (préparer Flutter) :
  - [ ] `GET /api/entries?month=YYYY-MM`
  - [ ] `POST /api/entries` (photo/note/link)

### Frontend (Inertia + Vue)
- [ ] Page `Calendar/Index.vue` → grille mensuelle avec compteur d’entries/jour
- [ ] Page `Day/Show.vue` → liste entries (cards dynamiques)
- [ ] Page `Entries/Create.vue` → select type + formulaire dynamique
- [ ] Composant `EntryCard.vue` → rendu selon type (photo, note, link)
- [ ] Page `Events/Show.vue` → infos + liste entries + carte (Leaflet)

### Importers
- [ ] Système `App\Importers\BaseImporter` + Job
- [ ] Importer CSV générique → crée entries + payload

### Tests
- [ ] Feature test : création note
- [ ] Feature test : upload photo avec EXIF
- [ ] Feature test : création événement
- [ ] Feature test : import CSV
- [ ] API test : `GET /api/entries?month=…`

### Bonus (si temps)
- [ ] Mini-statistiques mensuelles (nb entries par type)
- [ ] Mood tracker (payload dans notes)
- [ ] Carte interactive des entries
