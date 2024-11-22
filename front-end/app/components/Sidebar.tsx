"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import "bootstrap/dist/css/bootstrap.min.css";

interface User {
  id: number;
  nome: string;
  email: string;
  papel: string; // "admin" ou "professor"
}

const Sidebar: React.FC = () => {
  const router = useRouter();
  const [user, setUser] = useState<User | null>(null);

  // Recuperar os dados do usuário armazenados no localStorage
  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (userData) {
      try {
        setUser(JSON.parse(userData));
      } catch (error) {
        console.error("Erro ao recuperar os dados do usuário:", error);
        setUser(null);
      }
    }
  }, []);

  const handleLogout = () => {
    // Limpa os dados do usuário ao fazer logout
    localStorage.removeItem("user");
    router.push("/login");
  };

  if (!user) {
    return <div>Carregando...</div>;
  }

  return (
    <div className="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white" style={{ height: "100vh", width: "250px" }}>
      <h4 className="text-center mb-4">Painel</h4>
      <ul className="nav nav-pills flex-column mb-auto">
        <li className="nav-item">
          <Link href="/home" className="nav-link text-white">
            Home
          </Link>
        </li>

        {/* O admin tem acesso irrestrito */}
        {user.papel === "admin" && (
          <>
            <li>
              <Link href="/gerenciamentoUsuarios" className="nav-link text-white">
                Gerenciar Usuários
              </Link>
            </li>
            <li>
              <Link href="/Ambiente" className="nav-link text-white">
                Gerenciar Ambientes
              </Link>
            </li>
            <li>
              <Link href="/relatorios" className="nav-link text-white">
                Relatórios
              </Link>
            </li>
            <li>
              <Link href="/reservas" className="nav-link text-white">
                Gerenciar Reservas
              </Link>
            </li>
            <li>
              <Link href="/agenda" className="nav-link text-white">
                Visualizar Agenda
              </Link>
            </li>
          </>
        )}

        {/* O professor tem acesso restrito */}
        {user.papel === "professor" && (
          <>
            <li>
              <Link href="/reservas" className="nav-link text-white">
                Minhas Reservas
              </Link>
            </li>
            <li>
              <Link href="/agenda" className="nav-link text-white">
                Agenda
              </Link>
            </li>
            <li>
              <Link href="/Notificacoes" className="nav-link text-white">
                Notificações
              </Link>
            </li>
          </>
        )}

        <li>
          <button
            onClick={handleLogout}
            className="btn btn-danger w-100 mt-3"
          >
            Logout
          </button>
        </li>
      </ul>
    </div>
  );
};

export default Sidebar;
