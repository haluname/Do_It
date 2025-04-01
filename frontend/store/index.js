export const state = () => ({
  themeColor: '#fdf3e4',  // Colore tema
  userName: 'Anonimo'      // Nome utente
});

export const mutations = {
  SET_THEME_COLOR(state, color) {
    state.themeColor = color;
    localStorage.setItem('themeColor', color); // Salva nel localStorage
  },
  SET_USER_NAME(state, name) {
    state.userName = name;
    localStorage.setItem('userName', name); // Salva nel localStorage
  },
  LOAD_FROM_STORAGE(state) {
    state.themeColor = localStorage.getItem('themeColor') || state.themeColor;
    state.userName = localStorage.getItem('userName') || state.userName;
  }
};

export const actions = {
  nuxtServerInit({ commit }) {
    if (process.client) {
      commit('LOAD_FROM_STORAGE'); // Carica dati salvati dopo il refresh
    }
  }
};
