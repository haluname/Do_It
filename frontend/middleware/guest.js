export default function({ $auth, redirect, route }) {
    if ($auth.loggedIn) {
      return redirect('/home')
    }
    
    console.log(`Middleware guest: bloccato accesso a ${route.path} per utente autenticato`)
  }