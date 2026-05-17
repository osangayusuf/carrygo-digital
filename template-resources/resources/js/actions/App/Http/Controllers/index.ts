import HomeController from './HomeController'
import OpenBidsController from './OpenBidsController'
import HowToPlayController from './HowToPlayController'
import TermsController from './TermsController'
import Auth from './Auth'
import EventBidsController from './EventBidsController'
import TrendingBidsController from './TrendingBidsController'
import HistoryController from './HistoryController'
import LeaderboardBidsController from './LeaderboardBidsController'
import BidController from './BidController'
import ProfileController from './ProfileController'
import TaskCenterController from './TaskCenterController'
import CheckinController from './CheckinController'
import SpinController from './SpinController'
import RewardClaimController from './RewardClaimController'
const Controllers = {
    HomeController: Object.assign(HomeController, HomeController),
OpenBidsController: Object.assign(OpenBidsController, OpenBidsController),
HowToPlayController: Object.assign(HowToPlayController, HowToPlayController),
TermsController: Object.assign(TermsController, TermsController),
Auth: Object.assign(Auth, Auth),
EventBidsController: Object.assign(EventBidsController, EventBidsController),
TrendingBidsController: Object.assign(TrendingBidsController, TrendingBidsController),
HistoryController: Object.assign(HistoryController, HistoryController),
LeaderboardBidsController: Object.assign(LeaderboardBidsController, LeaderboardBidsController),
BidController: Object.assign(BidController, BidController),
ProfileController: Object.assign(ProfileController, ProfileController),
TaskCenterController: Object.assign(TaskCenterController, TaskCenterController),
CheckinController: Object.assign(CheckinController, CheckinController),
SpinController: Object.assign(SpinController, SpinController),
RewardClaimController: Object.assign(RewardClaimController, RewardClaimController),
}

export default Controllers