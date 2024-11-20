import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  // Obter o token do usuário
  const token = request.cookies.get('auth_token');

  // Redirecionar se não autenticado
  if (!token) {
    return NextResponse.redirect(new URL('/login', request.url));
  }

  // Decodificar token para verificar papel (opcional)
  const user = JSON.parse(Buffer.from(token.value.split('.')[1], 'base64').toString());

  // Verificar se é rota de admin
  const isAdminRoute = request.nextUrl.pathname.startsWith('/admin');
  if (isAdminRoute && user.papel !== 'admin') {
    return NextResponse.redirect(new URL('/', request.url)); // Redireciona para home se não for admin
  }

  return NextResponse.next(); // Permite continuar
}

export const config = {
  matcher: ['/admin/:path*', '/dashboard/:path*'], // Rotas protegidas pelo middleware
};
