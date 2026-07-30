import publicMethod from './public'
const tenant = {
    public: Object.assign(publicMethod, publicMethod),
}

export default tenant