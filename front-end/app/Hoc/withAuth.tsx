import { useRouter } from 'next/navigation';
import { useEffect } from 'react';

const withAuth = (WrappedComponent: React.FC, allowedRoles: string[] = []) => {
  const AuthenticatedComponent = (props: any) => {
    const router = useRouter();
    const user = typeof window !== 'undefined' ? JSON.parse(localStorage.getItem('user') || '{}') : null;

    useEffect(() => {
      // Verifica se o usuário está autenticado
      if (!user || !user.nome) {
        router.push('/login'); // Redireciona para login se não autenticado
        return;
      }

      // Verifica se a senha precisa ser redefinida
      if (user.senha_resetada === 'sim') {
        router.push('/login/alterarsenha'); // Redireciona para redefinição de senha
        return;
      }

      // Verifica se o papel do usuário é permitido
      if (allowedRoles.length > 0 && !allowedRoles.includes(user.papel)) {
        router.push('/home'); // Redireciona para home se não autorizado
      }
    }, [router, user]);

    // Enquanto o usuário não está validado, retorna null
    if (
      !user || 
      !user.nome || 
      user.senha_resetada === 'sim' || 
      (allowedRoles.length > 0 && !allowedRoles.includes(user.papel))
    ) {
      return null; // Evita renderizar a página até o redirecionamento
    }

    // Renderiza o componente protegido
    return <WrappedComponent {...props} />;
  };

  return AuthenticatedComponent;
};

export default withAuth;
