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

      <!-- Pulsante Logout -->
      <div class="logout-container">
        <v-list-item 
          @click="logout"
          class="logout-button"
        >
          <v-list-item-icon>
            <v-icon color="#ffd166">mdi-logout</v-icon>
          </v-list-item-icon>
          <v-list-item-content>
            <v-list-item-title class="logout-text">
              LOGOUT
            </v-list-item-title>
          </v-list-item-content>
        </v-list-item>
      </div>

    </v-navigation-drawer>

    <!-- Bottom Navigation per mobile -->
    <v-bottom-navigation 
    v-if="isMobile" 
    app 
    fixed 
    color="#34495e" 
    grow
    class="mobile-nav"
    :value="activeIndex"
  >
    <v-btn 
      v-for="(item, i) in navItems" 
      :key="i" 
      :to="item.route" 
      class="mobile-nav-btn"
      :active-class="'active-mobile-item'"
    >
      <v-icon :class="{'mobile-icon-active': $route.path === item.route}">
        {{ item.icon }}
      </v-icon>
      <span 
        class="nav-label"
        :class="{'label-active': $route.path === item.route}"
      >
        {{ item.title }}
      </span>
    </v-btn>

    <!-- Logout Mobile -->
    <v-btn 
      class="mobile-nav-btn"
      @click="logout"
    >
      <v-icon color="#ff6b6b">mdi-logout</v-icon>
      <span class="nav-label">Logout</span>
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
        { title: 'Goals', icon: 'mdi-folder-multiple', route: '/goals' },
        { title: 'Calendar', icon: 'mdi-calendar', route: '/calendar' },
        { title: 'TODAY', icon: 'mdi-star', route: '/today' },
      ],
      isMobile: false,
    };
  },
  computed: {
    activeIndex() {
      return this.navItems.findIndex(item => this.$route.path === item.route);
    }
  },
  methods: {
    async logout() {
      await this.$auth.logout()
      this.$router.push('/')
    },
    checkScreenSize() {
      this.isMobile = window.innerWidth <= 960;
    },
  },
  mounted() {
    this.checkScreenSize(); // Verifica la dimensione dello schermo al momento del montaggio
    window.addEventListener('resize', this.checkScreenSize); // Aggiungi un listener per il ridimensionamento
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.checkScreenSize); // Rimuovi il listener quando il componente è distrutto
  },
};
</script>


<style scoped>
.logout-container {
  position: absolute;
  bottom: 20px;
  width: 100%;
  border-top: 2px solid #2c3e56;
  padding-top: 10px;
}

.logout-button {
  transition: all 0.3s ease;
}

.logout-button:hover {
  background-color: #2c3e56 !important;
  cursor: pointer;
}

.logout-text {
  color: #ffd166 !important;
  font-weight: 600 !important;
  letter-spacing: 1px;
  font-size: 0.9rem !important;
}

.mobile-nav {
  border-top: 3px solid #ffd166 !important;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1) !important;
  backdrop-filter: blur(5px);
  background: rgba(52, 73, 94, 0.98) !important;
}

.mobile-nav-btn {
  min-width: 70px;
  padding: 8px 5px !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  flex-direction: column;
  height: auto !important;
}

.mobile-nav-btn .v-icon {
  font-size: 26px;
  margin-bottom: 2px;
  transition: all 0.3s ease;
  transform-origin: center;
}

.nav-label {
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.5px;
  opacity: 0.9;
  transition: all 0.3s ease;
}

.active-mobile-item {
  background: linear-gradient(0deg, rgba(255,209,102,0.15) 0%, rgba(255,209,102,0) 100%) !important;
}

.active-mobile-item .v-icon {
  color: #ffd166 !important;
  filter: drop-shadow(0 2px 4px rgba(255, 209, 102, 0.3));
}

.active-mobile-item .nav-label {
  color: #ffd166 !important;
  font-weight: 700 !important;
  opacity: 1;
  transform: translateY(1px);
}

.mobile-nav-btn:not(.active-mobile-item):hover {
  background-color: rgba(44, 62, 86, 0.6) !important;
}

.mobile-nav-btn:not(.active-mobile-item):hover .v-icon {
  transform: scale(1.05);
}

.mobile-icon-active {
  animation: icon-pulse 0.6s ease;
}

@keyframes icon-pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.15); }
  100% { transform: scale(1); }
}

.label-active {
  animation: label-pop 0.3s ease;
}

@keyframes label-pop {
  0% { transform: translateY(0); }
  50% { transform: translateY(-2px); }
  100% { transform: translateY(0); }
}

/* Logout Mobile */
.mobile-nav-btn:last-child .v-icon {
  color: #ff6b6b !important;
}

.mobile-nav-btn:last-child:hover .v-icon {
  animation: shake 0.6s ease;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}


</style>
