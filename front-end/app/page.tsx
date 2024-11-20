// app/page.tsx
import { redirect } from "next/navigation";

export default function Home() {
  redirect("/login"); // Redireciona automaticamente para a página de login
  return null; // Não vai renderizar nada, pois já faz o redirecionamento
}
