export const stubFetchingUserHeiims = cy => {
    cy.fixture('users/heiims.json').as('heiims');
    cy.route('GET', '/api/users/6', '@heiims');
};
