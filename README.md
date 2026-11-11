# CSRF lab

A small PHP/SQLite lab that demonstrates a cross-site request forgery (CSRF) against a state-changing money-transfer endpoint.

> **Warning:** This project is intentionally vulnerable. Run it only on a local machine or in an isolated training environment. Do not deploy it to a public or production server.

## What it demonstrates

The sample application, **Site A**, provides:

- Session-based login
- A seeded victim and attacker account
- A dashboard with account balances
- A money-transfer form handled by `transfer.php`

The transfer form does not use a CSRF token and `transfer.php` accepts any POST request that includes a valid session cookie. `exploit.html` acts as **Site B**: its disguised button submits a cross-origin request that transfers `$500.00` from the signed-in victim to the attacker.

## Requirements

- PHP 8.0 or newer
- PDO SQLite enabled
- A browser with JavaScript disabled or enabled; the demonstration uses a normal HTML form

## Run the lab

From the repository root, start Site A:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/setup.php` in a browser. This creates
`data/app.db` and seeds the following accounts:

| Username | Password | Initial balance |
| --- | --- | ---: |
| `victim` | `victim123` | `$1,000.00` |
| `attacker` | `attacker123` | `$0.00` |

Keep the Site A server running and, from the same repository root, start a
second server on another port so the exploit page has a different origin:

```bash
php -S localhost:8001
```

Then:

1. Open `http://localhost:8000/login.php` and sign in as `victim`.
2. Open `http://localhost:8001/exploit.html` in the same browser.
3. Click the free-iPhone button. The browser lands on Site A's dashboard,
   which reports `Sent $500.00 to attacker` and shows the reduced balance.
4. Optionally sign in as `attacker` to confirm the `$500.00` arrived.

The two ports make the pages different **origins**, while `localhost` keeps them in the same **site**, allowing the default `SameSite=Lax` cookie behavior used by this lab to be observed.

## Reset the lab

Visit `http://localhost:8000/setup.php` again. It drops and recreates the `users` table, restoring the original balances. The runtime database is stored at `data/app.db`.

## Relevant files

- `setup.php`: creates the SQLite database and seed accounts
- `login.php`: authenticates users and creates the session
- `dashboard.php`: displays the balance and transfer form
- `transfer.php`: intentionally vulnerable state-changing endpoint
- `exploit.html`: attacker-controlled cross-origin form (Site B)
- `bootstrap.php`: session and database initialization
- `db.php`: SQLite connection and authentication helpers
- `style.css`: shared Site A stylesheet

## How to fix the vulnerability

For a real application, protect every state-changing request with a server-generated, session-bound CSRF token and validate it before processing the transfer. Also consider enforcing appropriate `Origin`/`Referer` checks, using restrictive cookie settings where compatible, and requiring re-authentication or step-up confirmation for sensitive actions.

## License

No license has been specified for this repository.
