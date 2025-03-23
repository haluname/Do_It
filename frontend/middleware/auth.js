export default async function ({ redirect, store }) {
    const token = localStorage.getItem('auth_token');
    
    // ⛔ Se non c'è il token, reindirizza subito senza fare fetch
    if (!token) {
      return redirect('/');
    }

    // ⛔ Se l'utente è già nello store, evita la richiesta ripetuta
    if (store.state.user) {
      return;
    }

    try {
      const response = await fetch('http://localhost:8000/api/check', {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        localStorage.removeItem('auth_token');
        return redirect('/'); // 🔴 Redirige immediatamente se il token non è valido
      }

      const { user } = await response.json();
      store.commit('SET_USER', user);

    } catch (error) {
      localStorage.removeItem('auth_token');
      return redirect('/'); // 🔴 In caso di errore, reindirizza subito
    }
}
