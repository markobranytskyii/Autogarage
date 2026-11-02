<?php

// Alle meldingen op één plek, in het Nederlands.
const MELDINGEN = [
    // fouten
    'velden'          => ['fout', 'Vul alle verplichte velden in.'],
    'ongeldig'        => ['fout', 'Een of meer velden bevatten een ongeldige waarde.'],
    'inloggegevens'   => ['fout', 'E-mailadres of wachtwoord is onjuist.'],
    'inloggen'        => ['fout', 'Log in om deze pagina te bekijken.'],
    'bestaat'         => ['fout', 'Er bestaat al een account met dit e-mailadres.'],
    'wachtwoord'      => ['fout', 'Je wachtwoord moet minimaal 8 tekens lang zijn.'],
    'kenteken'        => ['fout', 'Dit kenteken is al geregistreerd.'],
    'monteur'         => ['fout', 'Kies een geldige monteur.'],
    'fout'            => ['fout', 'Er is iets misgegaan. Probeer het opnieuw.'],
    // succes
    'geregistreerd'   => ['succes', 'Je account is aangemaakt. Je kunt nu inloggen.'],
    'opgeslagen'      => ['succes', 'De wijzigingen zijn opgeslagen.'],
    'toegevoegd'      => ['succes', 'Het item is toegevoegd.'],
    'verwijderd'      => ['succes', 'Het item is definitief verwijderd.'],
    'uitgezet'        => ['succes', 'De dienst is uitgezet en staat nu in de prullenbak.'],
    'teruggezet'      => ['succes', 'De dienst is teruggezet.'],
    'uitgelogd'       => ['succes', 'Je bent uitgelogd.'],
];

// Toont de melding uit ?melding=... (als die bestaat).
function toonMelding(): void
{
    $code = $_GET['melding'] ?? '';
    if (!is_string($code) || !isset(MELDINGEN[$code])) {
        return;
    }

    [$soort, $tekst] = MELDINGEN[$code];
    echo '<p class="melding melding-' . $soort . '">' . htmlspecialchars($tekst) . '</p>';
}

// Stuurt terug naar een pagina met een melding, bijvoorbeeld stuurTerug('registreren.php', 'bestaat').
function stuurTerug(string $pagina, string $melding): void
{
    $scheiding = str_contains($pagina, '?') ? '&' : '?';
    header('Location: ' . $pagina . $scheiding . 'melding=' . urlencode($melding));
    exit;
}
