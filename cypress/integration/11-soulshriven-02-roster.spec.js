import { stubFetchingGroups } from "../fixtures/helpers/groups";
import { stubFetchingSets } from "../fixtures/helpers/sets";
import { stubFetchingSkills } from "../fixtures/helpers/skills";
import { stubFetchingUsers } from "../fixtures/helpers/users";
import { stubFetchingUserHeiims } from "../fixtures/helpers/users/heiims";
import { stubSoulshrivenUserAfterNameChange } from "../fixtures/helpers/users/soulshriven-after-name-change";

describe('Dashboard Screen for Soulshriven user', function () {
    it('visits Roster page', function () {
        cy.server();
        stubSoulshrivenUserAfterNameChange(cy);
        stubFetchingGroups(cy);
        stubFetchingSets(cy);
        stubFetchingSkills(cy);
        stubFetchingUsers(cy);
        cy.route('GET', '/api/users/@me/characters', []);

        cy.visit('/');
        cy.contains('Roster').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users');
        cy.get('h2').should('have.text', 'Fetching Roster information...');
        cy.get('h2').should('have.text', 'Roster');
        cy.get('ul.roster > li').should('have.length', 34);

        cy.get('ul.ne-corner button[title="Filter Actual Members"]').click();
        cy.get('ul.roster > li').should('have.length', 9);
        cy.get('ul.roster > li.soulshriven').should('have.length', 9);
        cy.get('ul.roster > li.members').should('have.length', 0);

        cy.get('ul.ne-corner button[title="Filter Soulshriven"]').click();
        cy.get('ul.ne-corner button[title="Filter Actual Members"]').click();
        cy.get('ul.roster > li.soulshriven').should('have.length', 0);
        cy.get('ul.roster > li.members').should('have.length', 25);

        stubFetchingUserHeiims(cy);
        cy.contains('@HEIIMS').click();
        cy.url().should('eq', 'http://planner.lodgeofsorceresses.test/users/6');
        cy.get('h2').should('have.text', '@HEIIMS');
        cy.get('dl > dt').should('have.text', 'Rank');
        cy.get('dl > dd').should('have.text', 'Adeptus Major');
        cy.get('table.character-list-table > tbody > tr').should('have.length', 5);
        cy.get('table.character-list-table > tbody > tr.no-clearance').should('have.length', 1);
        cy.get('table.character-list-table > tbody > tr.tier-4').should('have.length', 4);
        cy.get('table.character-list-table > tbody > tr:first-of-type > td > ul.action-list').should('be.hidden');
        cy.get('table.character-list-table > tbody > tr.no-clearance').click('right', {force: true});
        cy.get('table.character-list-table > tbody > tr:first-of-type > td > ul.action-list').should('be.visible');
    });
});
