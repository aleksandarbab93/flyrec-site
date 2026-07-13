# Flyrec Instagram Feed

WordPress plugin koji automatski prikazuje najnovije Instagram objave (Reels, video, foto, carousel) sa poslovnog Flyrec Instagram naloga na sajtu — preko **zvaničnog Instagram Graph API-ja**, bez scraping-a, bez korišćenja lozinke naloga.

---

## 1. Instalacija

1. U WordPress adminu idi na **Plugins → Add New → Upload Plugin**.
2. Izaberi `flyrec-instagram-feed.zip` i klikni **Install Now**, pa **Activate**.
3. U levom meniju pojaviće se nova stavka **📸 IG Feed**.

---

## 2. Priprema Instagram naloga

Instagram Graph API radi **samo sa Professional (Business ili Creator) nalogom** — lični profil ne može da se poveže.

- Otvori Instagram app → **Podešavanja → Nalog** → proveri da li već piše "Professional Account".
- Ako ne piše, izaberi **Prebaci na Professional nalog** i izaberi kategoriju (npr. "Video produkcija" / "Fotograf"). Besplatno je i traje par klikova.
- **Facebook stranica NIJE potrebna** — plugin koristi noviji "Instagram API with Instagram Login" flow koji radi direktno sa Instagram nalogom.

---

## 3. Kreiranje Meta Developer App-a

Ovo se radi jednom, na [developers.facebook.com](https://developers.facebook.com).

1. Uloguj se sa istim Facebook nalogom koji je povezan sa Instagram profilom (ili napravi novi Facebook nalog ako ga nemaš — Facebook nalog je potreban samo da se otvori Developer konzola, ne mora biti povezan sa IG-jem kao stranica).
2. **My Apps → Create App.**
3. Tip aplikacije: **"Other" → "Business"**.
4. Unesi ime app-a (npr. "Flyrec Website Feed") i potvrdi.
5. U dashboard-u nove aplikacije, pronađi karticu **"Instagram"** (product) i klikni **Set up**.
6. U Instagram product podešavanjima, sekcija **"Instagram API setup with Instagram login"** → prati čarobnjak:
   - Klikni **Add account** / **Generate access token** i poveži svoj `@flyrec` Instagram nalog (dobićeš zahtev direktno u Instagram app-u — prihvati ga).
   - Ovo automatski dodaje `@flyrec` kao **Instagram Tester nalog** u aplikaciji — to znači da app može da čita njegove podatke **bez Meta App Review procesa** (App Review je potreban samo ako aplikacija treba da radi za tuđe naloge, javno).
7. Na istoj stranici Meta ti nudi dugme **"Generate token"** — ovo generiše token koji odmah možeš da koristiš.

> Alternativa ako čarobnjak ne ponudi direktno generisanje: koristi zvanični **Graph API Explorer** (developers.facebook.com/tools/explorer) → izaberi svoju aplikaciju → izaberi Instagram nalog kao "User or Page" → pod Permissions dodaj `instagram_business_basic` (i `instagram_business_content_publish`/`instagram_business_manage_messages` NISU potrebni za ovaj plugin, preskoči ih) → **Generate Access Token**.

### Podešavanja specifična za App (opciono, samo za "Instagram embed" prikaz)

Ako želiš da koristiš **embed** način prikaza (Instagramov zvanični widget u popup-u, sa lajkovima/opisom u originalnom Instagram izgledu), plugin-u je potreban i:

- **App ID** i **App Secret** — oba se nalaze na app dashboard-u pod **Settings → Basic**.
- Unesi ih u plugin, sekciju **Napredno** (App ID) i pri povezivanju naloga (App Secret, opciono polje).

Bez ovoga, embed prikaz automatski pada nazad na "Otvori na Instagramu" link — sajt nikad ne puca zbog toga.

---

## 4. Povezivanje naloga u plugin-u

1. U WP adminu: **📸 IG Feed**.
2. U polje **Access Token** nalepi token koji si dobio/la u koraku 3.
3. (Opciono) U polje **App Secret** nalepi App Secret ako želiš da plugin automatski produži token na long-lived (60 dana) — ako to polje ostaviš prazno, plugin pretpostavlja da je nalepljeni token već long-lived (tako obično i jeste ako je dobijen kroz "Instagram API setup" čarobnjak).
4. Klikni **Sačuvaj i poveži**. Plugin odmah proverava token i, ako je ispravan, pokreće prvu sinhronizaciju.

### Šta ako token istekne?

Long-lived token traje **60 dana**. Plugin ga **automatski osvežava** (dnevni cron proverava i produžava token kad god istekne za manje od 10 dana) — dok god se sajt redovno posećuje (WP-Cron se pokreće na posetu) i sajt ostaje online, ne treba ništa ručno raditi.

Ako token ipak istekne (npr. sajt je bio dugo offline), admin ekran će jasno prikazati upozorenje — tada je potrebno ponoviti korak 3 (Graph API Explorer) i nalepiti nov token.

---

## 5. Podešavanje sinhronizacije i prikaza

Na istoj stranici (**📸 IG Feed**) podesi:

- **Interval sinhronizacije** — jednom na sat / dva puta dnevno / jednom dnevno / samo ručno.
- **Broj objava, broj kolona, tipovi sadržaja** (Reels/Video/Foto/Carousel), prikaz opisa/datuma, ponašanje nakon klika.

Dugme **Sinhronizuj sada** pokreće sinhronizaciju odmah, bez čekanja na cron.

Pojedinačne objave (isključi iz grida, promeni redosled) uređuju se pod **📸 IG Feed → Instagram objave** (standardna WP admin lista) — svaka objava ima polje "Redosled prikaza" i kućicu "Sakrij iz grida".

---

## 6. Prikaz na sajtu

### Shortcode

```
[flyrec_instagram_feed limit="12" columns="4" type="reels" click_action="lightbox"]
```

| Parametar      | Vrednosti                                              | Podrazumevano (iz podešavanja) |
|----------------|----------------------------------------------------------|---------------------------------|
| `limit`        | 1–50                                                     | 12                               |
| `columns`      | 1–6                                                      | 4                                |
| `type`         | `all`, `reels`, `video`, `image`, `carousel` (može više odvojeno zarezom, npr. `reels,video`) | `all` |
| `click_action` | `lightbox`, `embed`, `instagram`                        | `lightbox`                       |

### Gutenberg blok

U editoru dodaj blok **"Flyrec Instagram Feed"** (kategorija Widgets) — ista podešavanja kroz vizuelni panel, sa uživo pregledom.

### Elementor

Ako je Elementor aktivan, widget **"Flyrec Instagram Feed"** je dostupan u Elementor editoru (kategorija General).

---

## 7. Performanse i keširanje

- Instagram API se **ne poziva pri svakom učitavanju stranice** — samo periodično (cron) ili na ručni klik. Frontend uvek čita iz lokalne WordPress baze (CPT `flyrec_media`).
- Slike (thumbnail-ovi) se **hotlinkuju sa Instagram/Meta CDN-a** (ne kopiraju se automatski u Media Library) — sajt ostaje lagan, a poštuju se Instagram uslovi korišćenja. Ako želiš da ih kopiraš lokalno, to trenutno treba raditi ručno (nije uključeno u v1.0, videti napomenu ispod).
- Video se **ne hotlinkuje direktno** (Instagram CDN linkovi za video su privremeni/potpisani i mogu prestati da rade) — umesto toga, klik na video/reel/carousel lenjo učitava Instagram-ov zvanični embed widget, samo kad korisnik to zatraži.
- Sve slike u gridu koriste `loading="lazy"`.

---

## 8. Poznata ograničenja

- **Instagram Basic Display API je ugašen (decembar 2024)** — ovaj plugin zato koristi Instagram Graph API ("Instagram API with Instagram Login"), koji je trenutno zvanično podržan način.
- Nalog mora biti **Professional (Business/Creator)** — lični profili nisu podržani od strane Meta-e, ni za jedan zvanični metod.
- Rate limit Graph API-ja je dovoljno velik za periodičnu sinhronizaciju (nekoliko poziva na sat), ali API **ne treba pozivati po poseti sajta** — plugin to i ne radi.
- **Instagram Stories nisu podržane** ovim API-jem za automatsko preuzimanje (Meta ih namerno ne izlaže kroz media endpoint) — plugin prikazuje samo trajne objave (feed, reels, carousel).
- Puni OAuth "Connect" flow (klik-i-gotovo, bez ručnog kopiranja tokena) zahteva javan HTTPS domen registrovan kao redirect URI — nije uključen u v1.0 iz tog razloga; ručno lepljenje tokena je stabilnija alternativa za sajt sa jednim povezanim nalogom, i radi identično dobro.

---

## 9. Bezbednost

- Access token se čuva **enkriptovan** (AES-256-CBC, ključ izveden iz WordPress `AUTH_KEY`/`AUTH_SALT`), nikad kao čist tekst, nikad u HTML-u na frontend-u.
- Sve admin akcije zahtevaju `manage_options` dozvolu i WordPress nonce.
- Sav frontend/backend saobraćaj ide kroz WordPress HTTP API (`wp_remote_*`), bez direktnog cURL-a.
- Nema custom SQL upita — sve ide kroz WP_Query/postmeta API, koji interno koristi pripremljene upite.

---

## 10. Deinstalacija

Deaktivacija plugina (Plugins ekran) **ne briše ništa** — samo zaustavlja cron.

Potpuna deinstalacija (Delete) briše token/konekciju uvek, a sinhronizovane objave **samo ako je uključena kućica "Obriši sve podatke pri deinstalaciji"** u podešavanjima (podrazumevano isključeno, radi bezbednosti).
