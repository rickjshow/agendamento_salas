// app/login/page.tsx

"use client"; // Instrução para o Next.js tratar este arquivo como cliente

import { FormEvent, useState } from "react";
import Link from "next/link";
import axios from "axios";
import { useRouter } from "next/navigation"; // Importando o hook useRouter

const Login: React.FC = () => {
  const [email, setEmail] = useState<string>("");
  const [password, setPassword] = useState<string>("");
  const [error, setError] = useState<string | null>(null);
  const router = useRouter(); // Usando o hook useRouter para navegação

  const getCsrfToken = async (): Promise<string> => {
    const response = await axios.get("http://localhost:8000/api/csrf-token");
    return response.data.csrf_token;
  };

  const handleLogin = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);

    try {
      const csrfToken = await getCsrfToken();

      const response = await axios.post(
        "http://localhost:8000/api/login",
        { email, senha: password },
        {
          headers: {
            "X-CSRF-TOKEN": csrfToken,
          },
          withCredentials: true,
        }
      );

      // Armazenando os dados do usuário no localStorage
      localStorage.setItem("user", JSON.stringify(response.data.user));

      console.log(response.data);
      alert(`Bem-vindo, ${response.data.user.nome}!`);

      // Redireciona para a página /home após login
      router.push("/home");
    } catch (err: any) {
      setError(err.response?.data?.message || "Erro ao fazer login.");
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100">
      <div className="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <h1 className="text-2xl font-bold mb-4 text-center">Login</h1>
        <form onSubmit={handleLogin}>
          {error && (
            <div className="text-red-500 text-sm mb-4">
              {error}
            </div>
          )}
          <label className="block mb-2">Email:</label>
          <input
            type="email"
            placeholder="Digite seu email"
            className="w-full p-2 mb-4 border rounded"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
          />
          <label className="block mb-2">Senha:</label>
          <input
            type="password"
            placeholder="Digite sua senha"
            className="w-full p-2 mb-4 border rounded"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <button
            type="submit"
            className="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600"
          >
            Entrar
          </button>
        </form>
      </div>
    </div>
  );
};

export default Login;
