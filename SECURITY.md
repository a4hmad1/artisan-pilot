# Security

Do not disclose application secrets in public issues. Report a suspected package vulnerability privately through the repository's GitHub **Security → Advisories → Report a vulnerability**, if enabled. Otherwise contact the maintainer through their GitHub profile to arrange a private report before sharing details.

The package executes the selected application's Artisan CLI and therefore trusts that application, its packages, environment, aliases, and bootstrap code. Use it only with trusted projects. It does not sandbox application code or override Laravel's destructive-command policy. Review team alias files before use, especially stored `--force` options.

Only the current released package line receives fixes. PHP and framework versions retain their upstream support policies.
