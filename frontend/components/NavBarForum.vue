<template>
  <v-app-bar app color="white" elevate-on-scroll height="70" :color="navbarColor">
    <router-link to="/" class="d-flex align-center mr-5 logo">
      <v-img src="/img/logoBIG.svg" alt="Do!t Forum" max-height="45" max-width="120" contain
        style="filter: brightness(0) invert(0.3);"></v-img>
    </router-link>

    <div class="d-none d-md-flex align-center">
      <v-btn v-for="(item, i) in navItems" :key="i" :to="item.route ? item.route : undefined" :exact="item.exact"
        @click="handleNavClick(item)" text class="mr-2 nav-btn" active-class="active-link">
        <v-icon left small>{{ item.icon }}</v-icon>
        {{ item.text }}
      </v-btn>
    </div>

    <v-menu v-model="mobileMenu" offset-y transition="slide-y-transition" class="d-md-none" v-if="isMobile">
      <template v-slot:activator="{ on }">
        <v-btn icon v-on="on">
          <v-icon>mdi-menu</v-icon>
        </v-btn>
      </template>

      <v-list nav dense>
        <v-list-item two-line disabled>
          <v-list-item-content>
            <v-list-item-title>{{ $auth.user.name }}</v-list-item-title>
            <v-list-item-sub-title>
              <v-chip small :color="roleColor($auth.user.forum_role)" dark label outlined>
                <v-icon left x-small>mdi-account-box</v-icon>
                {{ $auth.user.forum_role }}
              </v-chip>
            </v-list-item-sub-title>
          </v-list-item-content>
        </v-list-item>

        <v-divider></v-divider>

        <v-list-item v-for="(item, i) in navItems" :key="i" :to="item.route" @click="mobileMenu = false">
          <v-list-item-icon>
            <v-icon small>{{ item.icon }}</v-icon>
          </v-list-item-icon>
          <v-list-item-title>{{ item.text }}</v-list-item-title>
        </v-list-item>

        <v-list-item @click="mobileMenu = false">
          <v-list-item-icon>
            <v-badge :content="unreadCount" color="red" overlap>
              <v-icon small>mdi-bell</v-icon>
            </v-badge>
          </v-list-item-icon>
          <v-list-item-title>Notifiche</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <v-spacer></v-spacer>

    <span class="d-none d-md-inline">Livello {{ $auth.user.level }}</span>

    <v-menu offset-y left transition="slide-y-transition" v-if="$auth.loggedIn">
      <template v-slot:activator="{ on }">
        <v-btn icon class="mr-3" v-on="on">
          <v-badge :content="unreadCount" color="red" overlap>
            <v-icon>mdi-bell</v-icon>
          </v-badge>
        </v-btn>
      </template>

      <v-card width="350">
        <v-toolbar color="orange lighten-5" dense flat>
          <v-toolbar-title class="orange--text text--darken-3">
            Notifiche
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn small text color="orange darken-3" @click="markAllAsRead" v-if="unreadCount > 0">
            Segna tutte come lette
          </v-btn>
        </v-toolbar>

        <v-list v-if="notifications.length > 0" three-line dense>
          <v-list-item v-for="notification in notifications" :key="notification.id"
            @click="handleNotificationClick(notification)" :class="{ 'grey lighten-4': !notification.read_at }">
            <v-list-item-content>
              <v-list-item-title class="font-weight-medium">
                {{ notification.message }}
              </v-list-item-title>
              <v-list-item-subtitle class="caption">
                {{ formatDate(notification.created_at) }}
              </v-list-item-subtitle>
            </v-list-item-content>
            <v-list-item-action>
              <v-icon v-if="!notification.read_at" color="orange darken-3">mdi-circle-small</v-icon>
            </v-list-item-action>
          </v-list-item>
        </v-list>

        <v-card-text v-else class="text-center py-4 grey--text">
          Nessuna notifica non letta
        </v-card-text>

        <v-divider></v-divider>
        <v-card-actions>
          <v-btn text small color="orange darken-3" @click="$router.push('/forum/notifications')">
            Vedi tutte
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-menu>

    <!-- Nome utente + Avatar + Ruolo (solo desktop) -->
    <v-chip class="d-none d-md-flex align-center" color="#f8e9d1" label
      style="font-weight: 600; font-size: 1.1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
      <v-avatar size="36" class="mr-2 rounded-circle" style="border: 2px solid #444444;">
        <span style="text-transform: uppercase; font-size: 0.9rem;">
          {{ $auth.user.name ? $auth.user.name[0] : '?' }}
        </span>
      </v-avatar>
      {{ $auth.user.name }}
      <v-chip :color="roleColor($auth.user.forum_role)" dark small class="ml-2" label outlined>
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
      {
        text: 'Home',
        icon: 'mdi-forum',
        route: '/forum',
        exact: true
      },
      {
        text: 'Wiki',
        icon: 'mdi-book',
        route: '/forum/wiki'
      },
      {
        text: 'Popolari',
        icon: 'mdi-fire',
        route: '/forum/popular'
      }
    ],
    notifications: [],
    unreadCount: 0
  }),
  mounted() {
    this.checkScreenSize();
    window.addEventListener('resize', this.checkScreenSize);

    if (this.$auth.loggedIn) {
      this.loadNotifications();
    }
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.checkScreenSize);
  },
  watch: {
    '$auth.loggedIn'(loggedIn) {
      if (loggedIn) {
        this.loadNotifications();
      } else {
        this.notifications = [];
        this.unreadCount = 0;
      }
    }
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
    },
    handleNavClick(item) {
      if (!item.route) {
        this.$emit('new-thread');
      }
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
      return new Date(dateString).toLocaleDateString('it-IT', options);
    },
    async loadNotifications() {
      try {
        const response = await this.$axios.get('/api/notifications', {
          params: { unread: true },
          headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
        });

        this.notifications = response.data.data;
        this.unreadCount = response.data.meta.unread_count;
      } catch (error) {
        console.error('Errore caricamento notifiche:', error);
      }
    },
    async markAsRead(notification) {
      try {
        await this.$axios.put(`/api/notifications/${notification.id}/read`, {}, {
          headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
        });

        notification.read_at = new Date().toISOString();
        this.unreadCount--;
      } catch (error) {
        console.error('Errore aggiornamento notifica:', error);
      }
    },
    async markAllAsRead() {
      try {
        await this.$axios.put('/api/notifications/mark-all-read', {}, {
          headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
        });

        this.notifications.forEach(n => n.read_at = new Date().toISOString());
        this.unreadCount = 0;
      } catch (error) {
        console.error('Errore aggiornamento notifiche:', error);
      }
    },
    async handleNotificationClick(notification) {
      if (!notification.read_at) {
        this.markAsRead(notification);
      }

      if (notification.notifiable_type === 'App\\Models\\Thread') {
        this.$router.push(`/forum/${notification.notifiable_id}`);
      } else if (notification.notifiable_type === 'App\\Models\\Post') {
        try {
          const response = await this.$axios.get(`/api/posts/${notification.notifiable_id}`);
          const threadId = response.data.thread.id;
          this.$router.push(`/forum/${threadId}`);
        } catch (error) {
          console.error('Errore nel recupero del post:', error);
          this.$toast.error('Impossibile trovare la discussione');
        }
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

.v-badge__badge {
  font-size: 10px;
  height: 18px;
  min-width: 18px;
  padding: 0 4px;
}

.unread-notification {
  background-color: #fff8e1;
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