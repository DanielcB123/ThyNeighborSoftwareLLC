import Auth from './Auth'
import ProfileController from './ProfileController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
ProfileController: Object.assign(ProfileController, ProfileController),
}

export default Controllers