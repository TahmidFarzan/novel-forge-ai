import axios from 'axios'

const axiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
    },
})

const token = document.head.querySelector('meta[name="csrf-token"]')

if (token) {
    axiosInstance.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

export default axiosInstance
