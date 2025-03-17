<template>
    <div class="page-container">
    <v-app-bar app fixed elevate-on-scroll class="main-navbar">
      <!-- Logo -->
      <router-link to="/" class="logo-link">
        <img src="/img/fullLogo.svg" alt="Logo" class="logo" />
      </router-link>
  
      <!-- Menu Desktop -->
      <div class="nav-links">
        <router-link 
          v-for="(item, index) in menuItems" 
          :key="index" 
          :to="item.path" 
          class="nav-item"
        >
          {{ item.title }}
        </router-link>
      </div>
  
      <!-- Menu Mobile -->
      <v-app-bar-nav-icon 
        @click="drawer = !drawer" 
        class="mobile-menu-icon"
      ></v-app-bar-nav-icon>
  
      <v-navigation-drawer v-model="drawer" temporary right fixed>
        <v-list nav dense>
          <v-list-item-group>
            <v-list-item 
              v-for="(item, index) in menuItems" 
              :key="index" 
              :to="item.path"
            >
              <v-list-item-title>{{ item.title }}</v-list-item-title>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-navigation-drawer>
  
      <!-- Call to Action -->
      <v-btn 
        color="primary" 
        class="cta-button" 
        @click="contactUs"
      >
        Contattaci
      </v-btn>
    </v-app-bar>
</div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        drawer: false,
        menuItems: [
          { title: 'Servizi', path: '/services' },
          { title: 'Chi Siamo', path: '/about' },
          { title: 'Portfolio', path: '/portfolio' },
          { title: 'Blog', path: '/blog' }
        ]
      }
    },
    methods: {
      contactUs() {
        // Logica per il click sul CTA
      }
    }
  }
  </script>
  
  <style scoped>

.page-container {
  background-color: #FEE267 
  ; /* Sostituisci con il colore desiderato */
  min-height: 100%;
  overflow-y: hidden;
  width: 100vw;
  margin: 0;
  padding: 20px; /* Regola il padding secondo necessità */
}

  .main-navbar {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1) !important;
    padding: 0 5%;
    transition: all 0.3s ease;
  
    .logo {
      height: 40px;
      transition: transform 0.3s ease;
  
      &:hover {
        transform: scale(1.05);
      }
    }
  
    .nav-links {
      display: flex;
      gap: 2rem;
      margin-left: 4rem;
  
      .nav-item {
        color: #2d3436;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 8px 0;
  
        &::after {
          content: '';
          position: absolute;
          bottom: 0;
          left: 0;
          width: 0;
          height: 2px;
          background: #0984e3;
          transition: width 0.3s ease;
        }
  
        &:hover::after,
        &.router-link-exact-active::after {
          width: 100%;
        }
  
        &.router-link-exact-active {
          color: #0984e3;
        }
      }
    }
  
    .cta-button {
      margin-left: auto;
      text-transform: none;
      font-weight: 600;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11);
      transition: all 0.3s ease;
  
      &:hover {
        transform: translateY(-1px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.2);
      }
    }
  
    @media (max-width: 960px) {
      .nav-links {
        display: none;
      }
  
      .cta-button {
        display: none;
      }
  
      .mobile-menu-icon {
        display: block !important;
      }
    }
  }
  </style>