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
        { title: 'Goals', icon: 'mdi-folder-multiple', route: '/goals' },
        { title: 'Calendar', icon: 'mdi-calendar', route: '/calendar' },
        { title: 'TODAY', icon: 'mdi-star', route: '/today' },
      ],
      isMobile: false,
    };
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
  // ... resto dello script rimane uguale
};
</script>

<style scoped>
/* Aggiungi questi stili */
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

/* Logout Style */
.logout-item {
  margin-top: 20px;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
}

.logout-item:hover {
  background-color: #2c3e56 !important;
  border-left-color: #ff6b6b;
}

.logout-text {
  color: #ff6b6b !important;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.v-list-item--active.logout-item {
  background-color: transparent !important;
}

/* Mobile Logout Icon */
.mobile-nav-btn .v-icon {
  transition: transform 0.2s ease;
}

.mobile-nav-btn:active .v-icon {
  transform: scale(1.1);
}
</style>