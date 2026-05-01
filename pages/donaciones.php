<?php
/**
 * StreamHub - Página de donaciones (Buy Me a Coffee)
 */
$bmcUser = 'slowdsports'; // ← Reemplaza con tu usuario de buymeacoffee.com
?>

<style>
.donate-hero {
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  padding: 3.5rem 0 2.5rem;
  text-align: center;
}
.donate-hero-icon {
  font-size: 3.5rem;
  line-height: 1;
  margin-bottom: 1rem;
  display: block;
}
.donate-hero h1 {
  font-family: 'Space Mono', monospace;
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 .6rem;
}
.donate-hero p {
  color: var(--text-muted);
  font-size: .95rem;
  max-width: 540px;
  margin: 0 auto;
  line-height: 1.6;
}

.donate-body {
  padding: 2.5rem 0 3rem;
}

.donate-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  margin-bottom: 2.5rem;
}
.donate-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 1.4rem;
  display: flex;
  gap: .9rem;
  align-items: flex-start;
}
.donate-card-icon {
  font-size: 1.4rem;
  flex-shrink: 0;
  line-height: 1;
  margin-top: 2px;
}
.donate-card h3 {
  font-size: .92rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 .3rem;
}
.donate-card p {
  font-size: .82rem;
  color: var(--text-muted);
  margin: 0;
  line-height: 1.5;
}

.donate-cta-box {
  background: var(--bg-card);
  border: 1px solid var(--border-accent);
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  max-width: 500px;
  margin: 0 auto;
}
.donate-cta-box h2 {
  font-family: 'Space Mono', monospace;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 .5rem;
}
.donate-cta-box p {
  font-size: .85rem;
  color: var(--text-muted);
  margin: 0 0 1.5rem;
}
.btn-bmc {
  display: inline-flex;
  align-items: center;
  gap: .6rem;
  background: #FFDD00;
  color: #000;
  font-weight: 800;
  font-size: .95rem;
  padding: .8rem 2rem;
  border-radius: 10px;
  text-decoration: none;
  transition: transform .15s, box-shadow .15s;
  box-shadow: 0 4px 14px rgba(255,221,0,.3);
}
.btn-bmc:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255,221,0,.45);
  color: #000;
}
.btn-bmc img {
  height: 22px;
  width: auto;
}
.donate-note {
  font-size: .75rem;
  color: var(--text-muted);
  margin-top: 1rem;
}
</style>

<!-- HERO -->
<section class="donate-hero">
  <div class="container">
    <span class="donate-hero-icon">☕</span>
    <h1>Apoya el Proyecto</h1>
    <p>Tele Deportes es un proyecto independiente mantenido con mucho esfuerzo. Tu apoyo nos permite seguir ofreciendo contenido en vivo de calidad.</p>
  </div>
</section>

<!-- CUERPO -->
<section class="donate-body">
  <div class="container">

    <div class="donate-cards">
      <div class="donate-card">
        <span class="donate-card-icon">📺</span>
        <div>
          <h3>Servidores en vivo</h3>
          <p>Mantenemos streams de alta calidad para que puedas ver tus partidos y canales favoritos sin interrupciones.</p>
        </div>
      </div>
      <div class="donate-card">
        <span class="donate-card-icon">🔒</span>
        <div>
          <h3>Sin anuncios para donadores</h3>
          <p>Con tu donación contribuyes a reducir la dependencia en publicidad y mejorar la experiencia de todos.</p>
        </div>
      </div>
      <div class="donate-card">
        <span class="donate-card-icon">⚡</span>
        <div>
          <h3>Desarrollo continuo</h3>
          <p>Cada apoyo nos motiva a agregar nuevos canales, ligas deportivas y mejorar las funciones de la plataforma.</p>
        </div>
      </div>
    </div>

    <div class="donate-cta-box">
      <h2>Invítanos un café ☕</h2>
      <p>Cada contribución, sin importar el monto, hace una gran diferencia para nosotros. Y te liberarás de los anuncios.</p>
      <a href="https://buymeacoffee.com/<?= htmlspecialchars($bmcUser) ?>" target="_blank" rel="noopener noreferrer" class="btn-bmc">
        <img src="https://cdn.buymeacoffee.com/buttons/bmc-new-btn-logo.svg" alt="Buy Me a Coffee">
        Donar con Buy Me a Coffee
      </a>
      <p class="donate-note">Serás redirigido a buymeacoffee.com · Pago seguro</p>
    </div>

  </div>
</section>

<!-- Widget flotante de Buy Me a Coffee -->
<script data-name="BMC-Widget"
        data-cfasync="false"
        src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js"
        data-id="<?= htmlspecialchars($bmcUser) ?>"
        data-description="Apoya Tele Deportes"
        data-message="¿Disfrutas el contenido? ¡Invítanos un café!"
        data-color="#8b5cf6"
        data-position="Right"
        data-x_margin="18"
        data-y_margin="18">
</script>
