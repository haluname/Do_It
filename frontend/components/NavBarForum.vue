<template>
  <v-app-bar app color="white" elevate-on-scroll height="70" :color="navbarColor">
    <!-- Logo -->
    <router-link to="/" class="d-flex align-center mr-5 logo">
      <v-img
        src="/img/logoBIG.svg"
        alt="Do!t Forum"
        max-height="45"
        max-width="120"
        contain
        style="filter: brightness(0) invert(0.3);"
      ></v-img>
    </router-link>

    <!-- Desktop Navigation -->
    <div class="d-none d-md-flex align-center">
      <v-btn
        v-for="(item, i) in navItems"
        :key="i"
        :to="item.route"
        text
        class="mr-2 nav-btn"
        active-class="active-link"
      >
        <v-icon left small>{{ item.icon }}</v-icon>
        {{ item.text }}
      </v-btn>
    </div>

    <!-- Mobile Menu Toggle -->
    <v-menu
      v-model="mobileMenu"
      offset-y
      transition="slide-y-transition"
      class="d-md-none"
      v-if="isMobile"
    >
      <template v-slot:activator="{ on }">
        <v-btn icon v-on="on">
          <v-icon>mdi-menu</v-icon>
        </v-btn>
      </template>

      <v-list nav dense>
        <!-- Utente + Ruolo nel menu mobile -->
        <v-list-item two-line disabled>
          <v-list-item-content>
            <v-list-item-title>{{ $auth.user.name }}</v-list-item-title>
            <v-list-item-sub-title>
              <v-chip
                small
                :color="roleColor($auth.user.forum_role)"
                dark
                label
                outlined
              >
                <v-icon left x-small>mdi-account-box</v-icon>
                {{ $auth.user.forum_role }}
              </v-chip>
            </v-list-item-sub-title>
          </v-list-item-content>
        </v-list-item>

        <v-divider></v-divider>

        <!-- Voci di navigazione nel menu mobile -->
        <v-list-item
          v-for="(item, i) in navItems"
          :key="i"
          :to="item.route"
          @click="mobileMenu = false"
        >
          <v-list-item-icon>
            <v-icon small>{{ item.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title>{{ item.text }}</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- Right Section -->
    <v-spacer></v-spacer>

    <!-- Livello utente (solo desktop) -->
    <span class="d-none d-md-inline">Livello {{ $auth.user.level }}</span>

    <!-- Nome utente + Avatar + Ruolo (solo desktop) -->
    <v-chip
      class="mx-3 d-none d-md-flex align-center"
      color="#f8e9d1"
      label
      style="font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.05);"
    >
      <v-avatar size="36" class="mr-2 rounded-circle" style="border: 2px solid #444444;">
        <span style="text-transform: uppercase; font-size: 0.9rem;">
          {{ $auth.user.name ? $auth.user.name[0] : '?' }}
        </span>
      </v-avatar>
      {{ $auth.user.name }}
      <v-chip
        :color="roleColor($auth.user.forum_role)"
        dark
        small
        class="ml-2"
        label
        outlined
      >
        {{ $auth.user.forum_role }}
      </v-chip>
    </v-chip>
  </v-app-bar>
</template>
<script>
export default {
  data: () => ({
    navbarColor: '#f8e9d1',
    isMobile: false,
    mobileMenu: false,
    navItems: [
      { text: 'Home', icon: 'mdi-forum', route: '/forum' },
      { text: 'Wiki', icon: 'mdi-book', route: '/forum/wiki' },
      { text: 'Popolari', icon: 'mdi-fire', route: '/forum/popular' },
      { text: 'Nuovo Topic', icon: 'mdi-plus-circle', route: '/forum/new' }
    ]
  }),
  mounted() {
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  methods: {
    checkScreenSize() {
      this.isMobile = window.innerWidth <= 960;
    },
    roleColor(role) {
      switch (role) {
        case 'Admin': return 'red';
        case 'mod': return 'orange';
        case 'member': return 'green';
        default: return 'blue';
      }
    }
  }
}
</script>

<style scoped>
* {
  font-family: "Uto-Bold", sans-serif !important;
}

.logo {
  padding-right: 15px;
}

.nav-btn {
  transition: all 0.3s ease;
  font-weight: 500;
  letter-spacing: 0.5px;
  color: rgba(91, 54, 15, 0.8) !important;
}

.nav-btn:hover {
  transform: translateY(-2px);
  color: #c45f00 !important;
}

.active-link {
  color: #c45f00 !important;
  background: rgba(196, 95, 0, 0.08) !important;
  border-radius: 8px;
}

.v-app-bar {
  border-bottom: 1px solid rgba(91, 54, 15, 0.1) !important;
  box-shadow: 0 2px 12px rgba(91, 54, 15, 0.08) !important;
  background-image: linear-gradient(to bottom, #f8e9d1 0%, #f5e2c4 100%);
}

::v-deep .v-toolbar__content {
  background-color: #f5e2c4 !important;
  border: 1px solid rgba(91, 54, 15, 0.1);
}

.logo {
  padding-right: 15px;
}

.nav-btn:hover {
  transform: translateY(-2px);
  color: #c45f00 !important;
  /* Arancione più scuro */
}

.active-link {
  color: #c45f00 !important;
  background: rgba(196, 95, 0, 0.08) !important;
  border-radius: 8px;
}

.v-app-bar {
  border-bottom: 1px solid rgba(91, 54, 15, 0.1) !important;
  box-shadow: 0 2px 12px rgba(91, 54, 15, 0.08) !important;
  background-image: linear-gradient(to bottom, #f8e9d1 0%, #f5e2c4 100%);
}

::v-deep .v-toolbar__content {
  background-color: #f5e2c4 !important;
  border: 1px solid rgba(91, 54, 15, 0.1);
}
</style>