"use client";

import React, { useState, useEffect } from "react";
import axios from "axios";
import Layout from "../components/Layout";
import withAuth from "../Hoc/withAuth";

const GerenciamentoUsuarios: React.FC = () => {
  const [usuarios, setUsuarios] = useState([]);
  const [modalAberto, setModalAberto] = useState(false);
  const [usuarioAtual, setUsuarioAtual] = useState<any>(null);
  const [form, setForm] = useState({
    nome: "",
    email: "",
    papel: "professor",
    status: "ativo",
  });

  // Buscar todos os usuários na API
  useEffect(() => {
    fetchUsuarios();
  }, []);

  const fetchUsuarios = async () => {
    try {
      const response = await axios.get("http://localhost:8000/api/usuarios");
      setUsuarios(response.data);
    } catch (error) {
      console.error("Erro ao buscar usuários:", error);
    }
  };

  // Abrir modal para criar ou editar usuário
  const abrirModal = (usuario: any = null) => {
    setUsuarioAtual(usuario);
    if (usuario) {
      setForm(usuario); // Preencher formulário para edição
    } else {
      setForm({ nome: "", email: "", papel: "professor", status: "ativo" });
    }
    setModalAberto(true);
  };

  // Fechar modal
  const fecharModal = () => {
    setModalAberto(false);
    setUsuarioAtual(null);
  };

  // Atualizar formulário
  const atualizarFormulario = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setForm({ ...form, [name]: value });
  };

  // Salvar novo usuário ou editar existente
  const salvarUsuario = async () => {
    try {
      if (usuarioAtual) {
        // Atualizar usuário existente
        await axios.put(`http://localhost:8000/api/usuarios/${usuarioAtual.id}/edit`, form);
      } else {
        // Criar novo usuário
        await axios.post("http://localhost:8000/api/usuarios/store", form);
      }
      fetchUsuarios();
      fecharModal();
    } catch (error) {
      console.error("Erro ao salvar usuário:", error);
    }
  };

  // Excluir usuário
  const excluirUsuario = async (id: number) => {
    if (confirm("Tem certeza que deseja excluir este usuário?")) {
      try {
        await axios.delete(`http://localhost:8000/api/usuarios/${id}`);
        fetchUsuarios();
      } catch (error) {
        console.error("Erro ao excluir usuário:", error);
      }
    }
  };

  // Resetar senha
  const resetarSenha = async (id: number) => {
    if (confirm("Tem certeza que deseja resetar a senha deste usuário?")) {
      try {
        await axios.post(`http://localhost:8000/api/usuarios/reset-password/${id}`);
        alert("Senha resetada com sucesso!");
      } catch (error) {
        console.error("Erro ao resetar senha:", error);
      }
    }
  };

  return (
    <Layout>
      <div className="container mx-auto p-4">
        <h1 className="text-2xl font-bold mb-4">Gerenciamento de Usuários</h1>
        <button
          onClick={() => abrirModal()}
          className="bg-blue-500 text-white px-4 py-2 rounded mb-4"
        >
          Adicionar Usuário
        </button>
        <table className="table-auto w-full border-collapse border border-gray-300">
          <thead>
            <tr>
              <th className="border border-gray-300 px-4 py-2">Nome</th>
              <th className="border border-gray-300 px-4 py-2">E-mail</th>
              <th className="border border-gray-300 px-4 py-2">Papel</th>
              <th className="border border-gray-300 px-4 py-2">Status</th>
              <th className="border border-gray-300 px-4 py-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            {usuarios.map((usuario: any) => (
              <tr key={usuario.id}>
                <td className="border border-gray-300 px-4 py-2">{usuario.nome}</td>
                <td className="border border-gray-300 px-4 py-2">{usuario.email}</td>
                <td className="border border-gray-300 px-4 py-2">{usuario.papel}</td>
                <td className="border border-gray-300 px-4 py-2">{usuario.status}</td>
                <td className="border border-gray-300 px-4 py-2">
                  <button
                    onClick={() => abrirModal(usuario)}
                    className="bg-green-500 text-white px-2 py-1 rounded mr-2"
                  >
                    Editar
                  </button>
                  <button
                    onClick={() => resetarSenha(usuario.id)}
                    className="bg-yellow-500 text-white px-2 py-1 rounded mr-2"
                  >
                    Resetar Senha
                  </button>
                  <button
                    onClick={() => excluirUsuario(usuario.id)}
                    className="bg-red-500 text-white px-2 py-1 rounded"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* Modal de Formulário */}
        {modalAberto && (
          <div className="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div className="bg-white p-6 rounded shadow-lg">
              <h2 className="text-xl font-bold mb-4">
                {usuarioAtual ? "Editar Usuário" : "Adicionar Usuário"}
              </h2>
              <form>
                <div className="mb-4">
                  <label className="block mb-2">Nome</label>
                  <input
                    type="text"
                    name="nome"
                    value={form.nome}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  />
                </div>
                <div className="mb-4">
                  <label className="block mb-2">E-mail</label>
                  <input
                    type="email"
                    name="email"
                    value={form.email}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  />
                </div>
                <div className="mb-4">
                  <label className="block mb-2">Papel</label>
                  <select
                    name="papel"
                    value={form.papel}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  >
                    <option value="professor">Professor</option>
                    <option value="admin">Administrador</option>
                  </select>
                </div>
                <div className="mb-4">
                  <label className="block mb-2">Status</label>
                  <select
                    name="status"
                    value={form.status}
                    onChange={atualizarFormulario}
                    className="w-full border border-gray-300 px-3 py-2 rounded"
                  >
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                  </select>
                </div>
              </form>
              <div className="flex justify-end">
                <button
                  onClick={salvarUsuario}
                  className="bg-blue-500 text-white px-4 py-2 rounded mr-2"
                >
                  Salvar
                </button>
                <button
                  onClick={fecharModal}
                  className="bg-gray-500 text-white px-4 py-2 rounded"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default withAuth(GerenciamentoUsuarios, ['admin']);
