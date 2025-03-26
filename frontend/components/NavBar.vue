<template>
    <div>
      <!-- Sidebar per schermi grandi -->
      <v-navigation-drawer 
        v-if="!isMobile"
        app 
        permanent 
        width="260" 
        color="#34495e" 
        dark 
        class="nav-drawer"
      >
        <div class="px-4 py-6 text-center">
          <v-img src="/img/logoBIG.svg" alt="Do!t Logo" max-width="120" class="mx-auto"></v-img>
        </div>
  
        <v-list dense nav class="mt-4">
          <v-list-item-group color="#ffd166">
            <v-list-item
              v-for="(item, i) in navItems"
              :key="i"
              :to="item.route"
              active-class="active-nav-item"
            >
              <v-list-item-icon>
                <v-icon>{{ item.icon }}</v-icon>
              </v-list-item-icon>
              <v-list-item-content>
                <v-list-item-title 
                  :class="{ 
                    'today-item': item.title === 'TODAY',
                    'rainbow-text': item.title === 'TODAY',
                    'item-list': true
                  }"
                >
                  {{ item.title }}
                </v-list-item-title>
              </v-list-item-content>
            </v-list-item>
          </v-list-item-group>
        </v-list>
      </v-navigation-drawer>
  
      <!-- Bottom Navigation per mobile -->
      <v-bottom-navigation v-if="isMobile" app fixed color="#34495e" grow>
        <v-btn 
          v-for="(item, i) in navItems" 
          :key="i" 
          :to="item.route" 
          class="mobile-nav-btn"
        >
          <v-icon>{{ item.icon }}</v-icon>
          <span class="nav-label">{{ item.title }}</span>
        </v-btn>
      </v-bottom-navigation>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        navItems: [
          { title: 'Home', icon: 'mdi-view-dashboard', route: '/dashboard' },
          { title: 'Goals', icon: 'mdi-folder-multiple', route: '/projects' },
          { title: 'Calendar', icon: 'mdi-calendar', route: '/calendar' },
          { title: 'TODAY', icon: 'mdi-star', route: '/today' },
        ],
        isMobile: false, // Stato per controllare la larghezza dello schermo
      };
    },
    mounted() {
      this.checkScreenSize();
      window.addEventListener('resize', this.checkScreenSize);
    },
    beforeDestroy() {
      window.removeEventListener('resize', this.checkScreenSize);
    },
    methods: {
      checkScreenSize() {
        this.isMobile = window.innerWidth <= 960; // Se la larghezza è inferiore a 960px, attiva la navbar mobile
      },
    },
  };
  </script>
  
  <style scoped>
  /* Stile per la sidebar */
  .nav-drawer {
    border-right: 3px solid #ffc641 !important;
  }
  
  /* Stile per la navbar attiva */
  .active-nav-item {
    background-color: #2c3e56 !important;
    color: #ffd166 !important;
    border-left: 4px solid #ffd166;
  }
  
  .v-list-item:hover {
    background-color: #2c3e56 !important;
  }
  
  .item-list {
    font-size: 14px !important;
  }
  
  /* Effetto rainbow per TODAY */
  @keyframes rainbow {
    0% { color: #ff0000; }
    20% { color: #ff9900; }
    40% { color: #ffff00; }
    60% { color: #33ff00; }
    80% { color: #0099ff; }
    100% { color: #cc00ff; }
  }
  
  .rainbow-text {
    animation: rainbow 10.5s linear infinite; 
    background-clip: text;
    -webkit-background-clip: text;
  }
  
  .today-item {
    font-size: 1.1em !important;
    font-weight: 900 !important;
    letter-spacing: 1px !important;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
  }
  
  /* Bottom Navigation Mobile */
  .v-bottom-navigation {
    z-index: 1000;
    border-top: 3px solid #ffc641 !important;
  }
  
  .mobile-nav-btn {
    flex-direction: column;
    height: auto !important;
    padding: 8px 0 !important;
  }
  
  .nav-label {
    font-size: 0.7rem;
    margin-top: 4px;
  }
  
  /* Nascondi sidebar su mobile */
  @media (max-width: 960px) {
    .nav-drawer {
      display: none !important;
    }
  }
  </style>
  