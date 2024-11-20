import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

export function middleware(req: NextRequest) {
  const userAuthCookie = req.cookies.get("user-auth");

  if (!userAuthCookie) {
    // Se não houver cookie de autenticação, redireciona para a página de login
    return NextResponse.redirect(new URL("/login", req.url));
  }

  try {
    const userData = JSON.parse(decodeURIComponent(userAuthCookie.value));
    const userRole = userData.user.papel; // "admin" ou "professor"
    const url = req.nextUrl.pathname;

    // Se for admin, não há restrição de acesso
    if (userRole === "admin") {
      return NextResponse.next();
    }

    // Se for professor, deve ter acesso a algumas páginas
    if (userRole === "professor") {
      // Impede o acesso a páginas restritas para o professor
      if (url.startsWith("/usuarios") || url.startsWith("/relatorios") || url.startsWith("/ambientes")) {
        return NextResponse.redirect(new URL("/home", req.url));
      }
    }

    return NextResponse.next();
  } catch (error) {
    console.error("Erro no middleware:", error);
    return NextResponse.redirect(new URL("/login", req.url));
  }
}
