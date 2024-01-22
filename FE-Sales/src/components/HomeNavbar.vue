<template>
  <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-light fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="/" style="font-size: 32px;">DjaMoeDje</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
        <ul class="navbar-nav mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link to="/login" class="nav-link"><button class="btn btn-primary">Masuk</button></router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'HomeNavbar'
}
</script>
