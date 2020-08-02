export const stubFetchingUserHeiims = cy => {
    cy.fixture('xhr-data/users/member-with-clearance.json').as('heiims');
    cy.route({
        method: 'GET',
        url: '/api/users/6',
        delay: 1000,
        response: '@heiims'
    }).as('loadHeiims');
};
