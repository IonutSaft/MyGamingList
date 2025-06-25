# MyGameWorld

Acesta este proiectul **MyGameWorld**, o rețea socială pentru comunitatea pasionaților de jocuri video.

## Adresa repository

- [https://github.com/IonutSaft/MyGamingList](https://github.com/IonutSaft/MyGamingList)

## Livrabile

- Codul sursă al aplicației (HTML, PHP, CSS, JavaScript)
- Acest fișier README cu instrucțiuni de instalare și utilizare

## Pași de compilare

Aplicația este dezvoltată în PHP și nu necesită compilare, însă trebuie să ai un server web ( de ex. Apache sau Nginx)
cu suport PHP și o bază de date MySQL. Dacă folosești XAMPP, WAMP, MAMP sau LAMP, urmează pașii de mai jos.

## Pași de instalare

1. **Clonează repository-ul:**

```bash
git clone https://github.com/IonutSaft/MyGamingList.git
```

2. **Configurează fișierele în root-ul serverului web:**
   Exemplu pentru WAMP:

- Pune conținutul în `C:\wamp\www\MyGameList`

3. **Configurează baza de date:**

- Creează o bază de date MySQL, de exemplu `mygamelist`
- Creează tabelele conform instrucțiunilor din fișierul `database.sql`
- Actualizează fișierul de configurare (`backend/db_connect.php`) cu datele de conectare la baza de date
- Rulează fișierul pentru popularea bazei de date pentru jocuri, folosind comanda următoare într-un terminal:
  ```bash
  php backend/igdb_request.php
  ```

4. **Configurează permisiunile:**
   Asigură-te că serverul web are permisiuni de scriere, întru-cât aplicația salvează fișiere local.

## Lansarea aplicației

1. Pornește serverul web și serverul MySQL (de exemplu, din XAMPP/WAMP/MAMP/LAMP).
2. Accesează aplicația în browser la adresa:

`http://localhost/MyGameList/homepage.php`

3. Înregistrează-te sau autentifică-te în aplicație și îți poți începe activitatea de a crea postări, urmării feed-ul sau gestiona colecția de jocuri!

---
