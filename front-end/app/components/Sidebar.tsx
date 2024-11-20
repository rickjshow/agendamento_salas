"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect, useState } from "react";
import styles from "../styles/Sidebar.module.css";

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
    return <div className={styles.sidebar}>Carregando...</div>;
  }

  return (
    <div className={styles.sidebar}>
      <nav>
        <ul>
          <li>
            <Link href="/home">Home</Link>
          </li>

          {/* O admin tem acesso irrestrito */}
          {user.papel === "admin" && (
            <>
              <li>
                <Link href="/usuarios">Gerenciar Usuários</Link>
              </li>
              <li>
                <Link href="/ambientes">Gerenciar Ambientes</Link>
              </li>
              <li>
                <Link href="/relatorios">Relatórios</Link>
              </li>
              <li>
                <Link href="/reservas">Gerenciar Reservas</Link>
              </li>
              <li>
                <Link href="/agenda">Visualizar Agenda</Link>
              </li>
            </>
          )}

          {/* O professor tem acesso restrito */}
          {user.papel === "professor" && (
            <>
              <li>
                <Link href="/reservas">Minhas Reservas</Link>
              </li>
              <li>
                <Link href="/agenda">Visualizar Agenda</Link>
              </li>
            </>
          )}

          <li>
            <button onClick={handleLogout} className={`${styles.logoutButton}`}>
              Logout
            </button>
          </li>
        </ul>
      </nav>
    </div>
  );
};

export default Sidebar;
