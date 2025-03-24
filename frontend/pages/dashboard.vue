<template>
  <v-app>
    <v-navigation-drawer
      app
      permanent
      width="260"
      color="#34495e"
      dark
      class="nav-drawer"
    >
      <div class="px-4 py-6 text-center">
        <v-img
          src="/img/logoBIG.svg"
          alt="Do!t Logo"
          max-width="120"
          class="mx-auto"
        ></v-img>
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

    <v-bottom-navigation
      v-if="isMobile"
      app
      grow
      color="#ffd166"
      background-color="#34495e"
      class="mobile-nav"
    >
      <v-btn
        v-for="(item, i) in navItems"
        :key="i"
        :to="item.route"
        value="recent"
      >
        <span 
          class="nav-label"
          :class="{ 
            'today-item-mobile': item.title === 'TODAY',
            'rainbow-text': item.title === 'TODAY'
          }"
        >
          {{ item.title }}
        </span>
        <v-icon>{{ item.icon }}</v-icon>
      </v-btn>
    </v-bottom-navigation>

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Card Content -->
        <v-card
          elevation="6"
          rounded="lg"
          class="mx-auto pa-4"
          :class="{ 'mobile-card': isMobile }"
          color="#fff2b8"
        >
          <!-- Mobile Header -->
          <div v-if="isMobile" class="text-center mb-4">
            <v-img
              src="/img/logoBIG.svg"
              alt="Do!t Logo"
              max-width="80"
              class="mx-auto"
            ></v-img>
          </div>

          <div class="text-center mb-6">
            <v-avatar :size="isMobile ? 80 : 120" color="#ffd166">
              <v-icon dark :size="isMobile ? 40 : 64">mdi-account-circle</v-icon>
            </v-avatar>
            <h1 class="text-h4 mt-4 primary--text" :class="{ 'text-h5': isMobile }">
              Welcome {{ $auth.user.name }}
            </h1>
            <p class="text-h6 mt-2 secondary--text" :class="{ 'text-body-1': isMobile }">
              {{ $auth.user.email }}
            </p>
          </div>

          <v-divider class="my-4"></v-divider>

          <div class="text-center">
            <v-btn
              color="secondary"
              :x-large="!isMobile"
              large
              rounded
              @click="logout"
              class="px-8"
              :block="isMobile"
            >
              <v-icon left>mdi-logout</v-icon>
              Logout
            </v-btn>
          </div>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  data: () => ({
    navItems: [
      { title: 'Home', icon: 'mdi-view-dashboard', route: '/dashboard' },
      { title: 'Goals', icon: 'mdi-folder-multiple', route: '/projects' },
      { title: 'Calendar', icon: 'mdi-calendar', route: '/calendar' },
      { title: 'TODAY', icon: 'mdi-star', route: '/today' },  // Cambiata icona a stella
    ],
  }),
  computed: {
    isMobile() {
      return this.$vuetify.breakpoint.smAndDown
    }
  },
  methods: {
    async logout() {
      await this.$auth.logout()
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>

*{
  font-family: 'Uto-Bold', sans-serif !important; 
}

.item-list{
  font-size: 14px !important;
}

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

.today-item-mobile {
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  margin-top: 2px !important;
}

/* Adatta l'icona TODAY */
.v-list-item[to="/today"] .v-icon {
  color: #ffd166 !important;
  transform: scale(1.1);
}

/* Effetto hover per TODAY */
.v-list-item[to="/today"]:hover {
  background-color: #2c3e56 !important;
  transform: scale(1.02);
  transition: all 0.3s ease;
}

/* Mobile specific adjustments */
.mobile-nav .rainbow-text {
  animation: rainbow 2.5s linear infinite;
}

.mobile-nav .v-btn[to="/today"] .v-icon {
  color: #ffd166 !important;
  transform: scale(1.2);
}
.nav-drawer {
  border-right: 3px solid #ffc641 !important;
}

.active-nav-item {
  background-color: #2c3e56 !important;
  color: #ffd166 !important;
  border-left: 4px solid #ffd166;
}

.v-list-item:hover {
  background-color: #2c3e56 !important;
}

.primary--text {
  color: #34495e !important;
}

.secondary--text {
  color: #444444 !important;
}

.v-btn {
  letter-spacing: 1px;
  font-weight: bold;
  text-transform: uppercase;
}

.v-btn--active:before {
  background-color: transparent;
}

.v-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-card:hover {
  transform: translateY(-4px);
}

.v-avatar {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.mobile-nav {
  border-top: 3px solid #ffd166 !important;
  z-index: 1000;
}

.mobile-nav .v-btn {
  flex-direction: column;
  height: auto !important;
  padding: 8px 0 !important;
}

.mobile-nav .nav-label {
  font-size: 0.7rem;
  margin-top: 4px;
}

.mobile-card {
  border-radius: 16px !important;
  margin: 8px;
  padding: 16px !important;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .nav-drawer {
    display: none !important;
  }

  .v-main {
    padding-bottom: 56px !important; /* Altezza bottom navigation */
  }
}
</style>