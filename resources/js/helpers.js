export const authorizeAdmin = ({ me, groups }) => {
    if (!me || !groups || !me.linkedAccountsParsed.ips) {
        return false;
    }

    const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]['ipsGroupId']);

    return !(!myGroup || !myGroup[1]['isAdmin']);
};

export const authorizeUser = ({ me, groups }, withAdditionalPrechecks = false) => {
    if (!me || !groups || !me.linkedAccountsParsed.discord) {
        return false;
    }

    if (withAdditionalPrechecks && (!me.name || !me.name.length)) {
        return false;
    }

    const discordGroups = me.linkedAccountsParsed.discord.remote_secondary_groups;
    if (discordGroups && discordGroups.length) {
        const listOfUserGroups = discordGroups.find(discordRole => {
            const matchingGroup = Object.entries(groups).find(group => discordRole === group[1]['discordRole']);

            return matchingGroup === undefined ? false : matchingGroup['1']['isMember'];
        });

        return listOfUserGroups !== undefined;
    }

    return false;
};
