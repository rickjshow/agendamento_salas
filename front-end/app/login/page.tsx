"use client";

import { FormEvent, useState } from "react";
import axios from "axios";
import { useRouter } from "next/navigation";

const Login: React.FC = () => {
  const [email, setEmail] = useState<string>("");
  const [password, setPassword] = useState<string>("");
  const [error, setError] = useState<string | null>(null);
  const router = useRouter();

  const handleLogin = async (e: FormEvent) => {
    e.preventDefault();
    setError(null);

    try {
      const response = await axios.post(
        "http://localhost:8000/api/login",
        { email, senha: password },
        {
          withCredentials: true, // Permite cookies, se necessário
        }
      );

      const { user } = response.data;

      // Verificar se a senha precisa ser redefinida
      if (user.senha_resetada === "sim") {
        localStorage.setItem("user", JSON.stringify(user));
        router.push("/login/alterarsenha"); // Redireciona para a redefinição de senha
        return;
      }

      // Login bem-sucedido
      localStorage.setItem("user", JSON.stringify(user));
      alert(`Bem-vindo, ${user.nome}!`);
      router.push("/home");
    } catch (err: any) {
      // Tratar mensagens de erro específicas
      if (err.response?.status === 403) {
        setError("Seu usuário está inativo. Entre em contato com o administrador.");
      } else {
        setError(err.response?.data?.message || "Erro ao fazer login.");
      }
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-100">
      <div className="w-full max-w-sm bg-white rounded-lg shadow-md p-6">
        <h1 className="text-2xl font-bold mb-4 text-center">Login</h1>
        <form onSubmit={handleLogin}>
          {error && <div className="text-red-500 text-sm mb-4">{error}</div>}
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
