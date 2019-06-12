export const amIAdmin = ({me, groups}) => {
    if (!me || !groups || !me.linkedAccountsParsed.ips) {
        return;
    }

    const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]['ipsGroupId']);

    return !(!myGroup || !myGroup[1]['isAdmin']);
};
