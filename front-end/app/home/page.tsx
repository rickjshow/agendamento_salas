"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Layout from "../components/Layout"; // Importando o Layout

const Home: React.FC = () => {
  const router = useRouter();
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | null>(null); // Usamos null para representar estado de carregamento

  useEffect(() => {
    const userData = localStorage.getItem("user");
    if (!userData) {
      // Se não houver dados do usuário, redireciona para o login
      router.push("/login");
    } else {
      setIsAuthenticated(true);
    }
  }, [router]);

  // Enquanto o estado de autenticação não for definido (null), exibe o loading
  if (isAuthenticated === null) {
    return <div>Loading...</div>;
  }

  return (
    <Layout>
      <div>
        <h1>Bem-vindo à Home</h1>
        {/* Conteúdo da página */}
      </div>
    </Layout>
  );
};

export default Home;
