# Quimicraft-IFC

Jogo educativo sobre funções inorgânicas para alunos do Ensino Médio.

## Estrutura do projeto

```
Quimicraft-IFC/
├── index.html          # Redireciona para html/index.html
├── html/               # Páginas públicas (HTML)
│   ├── index.html
│   ├── login.html
│   └── cadastro.html
├── php/                # Páginas e scripts PHP
│   ├── conexao.php
│   ├── logar.php
│   ├── cadastrar.php
│   ├── sair.php
│   ├── principal.php
│   ├── jogo_solo.php
│   ├── ranked.php
│   ├── ranking.php
│   ├── salvar_pontuacao_ranked.php
│   └── consultar_ranking.php
├── css/
│   └── style.css
├── js/
│   ├── jogo_solo.js
│   └── jogo_ranked.js
├── assets/
│   └── bg3.png
└── sql/
    ├── banco.sql
    └── migracao_ranked.sql
```

## Acesso

1. Importe `sql/banco.sql` no MySQL (banco `quimicraft`).
2. Acesse: `http://localhost/Quimicraft-IFC/`
3. Após login, o menu principal fica em `php/principal.php`.
