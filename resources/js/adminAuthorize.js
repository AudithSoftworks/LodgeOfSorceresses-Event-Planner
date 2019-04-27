const adminAuthorize = ({ me, groups, history }) => {
    if (!me || !groups || !me.linkedAccountsParsed.ips) {
        return;
    }

    const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]['ipsGroupId']);
    if (!myGroup || !myGroup[1]['isAdmin']) {
        return history.push('/');
    }
};

export default adminAuthorize;
